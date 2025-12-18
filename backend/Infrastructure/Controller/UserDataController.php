<?php

require_once __DIR__ . '/../../Application/DTO/GetUserReservationsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetUserSessionsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetReservationInvoiceDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetUserSubscriptionsDTO.php';
require_once __DIR__ . '/../Repositories/ReservationRepository.php';
require_once __DIR__ . '/../Repositories/SessionRepository.php';
require_once __DIR__ . '/../Repositories/SubscriptionRepository.php';

class UserDataController {
    private PDO $pdo;

    private ReservationRepository $reservationRepo;
    private SessionRepository $sessionRepo;
    private SubscriptionRepository $subscriptionRepo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getUserReservations(GetUserReservationsDTO $dto): array {
        try {
            $this->reservationRepo = new ReservationRepository($this->pdo);
            $this->sessionRepo = new SessionRepository($this->pdo);
            $this->subscriptionRepo = new SubscriptionRepository($this->pdo);
            $reservations = $this->reservationRepo->findByUserWithParking($dto->userId);
            // compute DB now timestamp and a grace window for lateness detection
            $graceMinutes = 10;
            $dbNowRow = $this->pdo->query("SELECT UNIX_TIMESTAMP(NOW()) AS now_ts")->fetch(PDO::FETCH_ASSOC);
            $dbNowTs = isset($dbNowRow['now_ts']) ? (int)$dbNowRow['now_ts'] : time();

            return [
                'success' => true,
                'reservations' => array_map(function($r) use ($dbNowTs, $graceMinutes) {
                    $status = $r['status'];

                    // Vérifier si l'utilisateur est actuellement dans le parking pour cette réservation
                    $inParking = false;
                    try {
                        $s = $this->sessionRepo->getOpenSessionByReservation((int)$r['id']);
                        $inParking = (bool)$s;
                    } catch (Exception $e) {
                        $inParking = false;
                    }

                    // Determine timestamps
                    $startTs = isset($r['start_time']) ? (int)strtotime($r['start_time']) : null;
                    $endTs = isset($r['end_time']) ? (int)strtotime($r['end_time']) : null;

                    // Determine if reservation is late: pending and now > start_time + grace
                    $isLate = false;
                    if ($status === 'pending' && $startTs !== null) {
                        if ($dbNowTs > ($startTs + ($graceMinutes * 60))) {
                            $isLate = true;
                        }
                    }

                    // Compute displayStatus and displayLabel with precedence:
                    // inParking -> 'Dans le parking'
                    // completed/status='completed' -> 'Terminé'
                    // pending & now < start -> 'A venir'
                    // pending & now between start and end -> 'En cours' (unless isLate -> 'En retard')
                    // active & now between start and end -> 'En cours'
                    // otherwise -> 'Terminé'
                    $displayStatus = $status;
                    $displayLabel = ucfirst($status);

                    if ($inParking) {
                        $displayStatus = 'in_parking';
                        $displayLabel = 'Dans le parking';
                    } elseif (($r['status'] ?? '') === 'completed') {
                        $displayStatus = 'completed';
                        $displayLabel = 'Terminé';
                    } else {
                        if ($startTs !== null && $endTs !== null) {
                            if ($dbNowTs < $startTs) {
                                $displayStatus = 'pending';
                                $displayLabel = 'À venir';
                            } elseif ($dbNowTs >= $startTs && $dbNowTs <= $endTs) {
                                if ($isLate) {
                                    $displayStatus = 'late';
                                    $displayLabel = 'En retard';
                                } else {
                                    $displayStatus = 'active';
                                    $displayLabel = 'En cours';
                                }
                            } else {
                                $displayStatus = 'completed';
                                $displayLabel = 'Terminé';
                            }
                        }
                    }

                    return [
                        'id' => $r['id'],
                        'parkingId' => $r['parking_id'],
                        'parkingName' => $r['parking_name'],
                        'parkingAddress' => $r['parking_address'],
                        'startTime' => $r['start_time'],
                        'endTime' => $r['end_time'],
                        'totalPrice' => $r['total_price'],
                        'status' => $status,
                        'inParking' => $inParking,
                        'isLate' => $isLate,
                        'displayStatus' => $displayStatus,
                        'displayLabel' => $displayLabel
                    ];
                }, $reservations),
                'count' => count($reservations)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getUserSessions(GetUserSessionsDTO $dto): array {
        try {
            $this->sessionRepo = new SessionRepository($this->pdo);
            $sessions = $this->sessionRepo->findByUser($dto->userId);

            return [
                'success' => true,
                'sessions' => array_map(function($s) {
                    return [
                        'id' => $s['id'],
                        'parkingId' => $s['parking_id'],
                        'parkingName' => $s['parking_name'],
                        'parkingAddress' => $s['parking_address'],
                        'entryTime' => $s['entry_time'],
                        'exitTime' => $s['exit_time'],
                        'isActive' => $s['exit_time'] === null
                    ];
                }, $sessions),
                'count' => count($sessions)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getReservationInvoice(GetReservationInvoiceDTO $dto): array {
        try {
            // Récupérer la réservation
            $reservation = $this->reservationRepo->findByIdWithDetails($dto->reservationId);

            if (!$reservation) {
                return ['success' => false, 'message' => 'Réservation non trouvée.'];
            }

            // N'autoriser la facture que pour les réservations terminées
            if (($reservation['status'] ?? '') !== 'completed') {
                return ['success' => false, 'message' => 'Facture disponible uniquement pour les réservations terminées.'];
            }

            // Calculer le montant
            $start = new DateTime($reservation['start_time']);
            $end = new DateTime($reservation['end_time']);
            $hours = max(1, ($end->getTimestamp() - $start->getTimestamp()) / 3600);
            $amount = $reservation['total_price'] ?? round($hours * $reservation['hourly_rate'], 2);

            $invoice = [
                'invoiceNumber' => 'INV-' . str_pad($reservation['id'], 6, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d'),
                'customer' => [
                    'name' => $reservation['first_name'] . ' ' . $reservation['last_name'],
                    'email' => $reservation['email']
                ],
                'parking' => [
                    'name' => $reservation['parking_name'],
                    'address' => $reservation['parking_address']
                ],
                'reservation' => [
                    'id' => $reservation['id'],
                    'startTime' => $reservation['start_time'],
                    'endTime' => $reservation['end_time'],
                    'duration' => round($hours, 2) . ' heures',
                    'hourlyRate' => $reservation['hourly_rate']
                ],
                'amount' => $amount,
                'currency' => 'EUR'
            ];

            if ($dto->format === 'html') {
                $html = $this->generateInvoiceHtml($invoice);
                return ['success' => true, 'invoice' => $invoice, 'html' => $html];
            }

            return ['success' => true, 'invoice' => $invoice];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function generateInvoiceHtml(array $invoice): string {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h1 style='color: #8e24aa;'>Facture {$invoice['invoiceNumber']}</h1>
            <p>Date: {$invoice['date']}</p>
            <hr>
            <h3>Client</h3>
            <p>{$invoice['customer']['name']}<br>{$invoice['customer']['email']}</p>
            <h3>Parking</h3>
            <p>{$invoice['parking']['name']}<br>{$invoice['parking']['address']}</p>
            <h3>Détails de la réservation</h3>
            <p>Du: {$invoice['reservation']['startTime']}<br>
               Au: {$invoice['reservation']['endTime']}<br>
               Durée: {$invoice['reservation']['duration']}</p>
            <hr>
            <h2 style='text-align: right;'>Total: {$invoice['amount']} {$invoice['currency']}</h2>
        </div>";
    }

    public function getUserSubscriptions(GetUserSubscriptionsDTO $dto): array {
        try {
            $subscriptions = $this->subscriptionRepo->findUserSubscriptions($dto->userId);

            return [
                'success' => true,
                'subscriptions' => array_map(function($s) {
                    return [
                        'id' => $s['id'],
                        'typeName' => $s['type_name'],
                        'description' => $s['description'],
                        'monthlyPrice' => $s['monthly_price'],
                        'durationMonths' => $s['duration_months'],
                        'parkingName' => $s['parking_name'],
                        'parkingAddress' => $s['parking_address'],
                        'startDate' => $s['start_date'],
                        'endDate' => $s['end_date'],
                        'status' => $s['status']
                    ];
                }, $subscriptions),
                'count' => count($subscriptions)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
