<?php

require_once __DIR__ . '/../../Application/DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../Repositories/ReservationRepository.php';
require_once __DIR__ . '/../Repositories/SessionRepository.php';
require_once __DIR__ . '/../Repositories/InvoiceRepository.php';
require_once __DIR__ . '/../Repositories/ParkingRepository.php';

class ParkingSpaceController {
    private PDO $pdo;
    private ParkingRepository $parkingRepo;
    private ReservationRepository $reservationRepo;
    private SessionRepository $sessionRepo;
    private InvoiceRepository $invoiceRepo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->parkingRepo = new ParkingRepository($pdo);
        $this->reservationRepo = new ReservationRepository($pdo);
        $this->sessionRepo = new SessionRepository($pdo);
        $this->invoiceRepo = new InvoiceRepository($pdo);
    }

    public function enterExitParking(EnterExitParkingDTO $dto): array {
        try {
            // Vérifier que le parking existe
            $parking = $this->parkingRepo->findById((int)$dto->parkingId);
            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            $userId = $dto->userId ?? null;
            if (!$userId || !is_int($userId) || $userId <= 0) {
                return ['success' => false, 'message' => 'Utilisateur non authentifié.'];
            }

            if ($dto->action === 'enter') {
                // Ensure logs directory exists
                $logDir = __DIR__ . '/../../../logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0777, true);
                // Use the database server time for comparisons to match reservation timestamps
                $dbNowRow = $this->pdo->query("SELECT NOW() as now")->fetch(PDO::FETCH_ASSOC);
                $now = new DateTime($dbNowRow['now']);
                // Trouver d'abord des réservations candidates puis comparer en PHP
                $graceMinutes = 10; // tolérance avant début de réservation
                // graceMinutes used for certain computations

                // DB now and PHP now are available if needed

                $reservationRepo = $this->reservationRepo;
                $sessionRepo = $this->sessionRepo;
                $invoiceRepo = $this->invoiceRepo;

                // Find reservation (status active OR currently in time window)
                $reservation = $reservationRepo->findActiveOrOngoingByParkingAndUser((int)$dto->parkingId, $userId);
                if (!$reservation) {
                    return ['success' => false, 'message' => 'Aucune réservation en cours. Vous ne pouvez pas entrer.'];
                }

                // Vérifier qu'il n'existe pas déjà une session ouverte pour cet utilisateur et ce parking
                $existing = $sessionRepo->getOpenSession((int)$dto->parkingId, $userId);
                if ($existing) {
                    return ['success' => false, 'message' => 'Vous avez déjà une session ouverte pour ce parking.'];
                }

                // Enregistrer l'entrée dans parkings_sessions (associée à la réservation trouvée)
                $resId = isset($reservation['id']) ? (int)$reservation['id'] : null;
                $sessionId = $sessionRepo->createSession((int)$dto->parkingId, $userId, $resId);

                return [
                    'success' => true,
                    'message' => 'Entrée enregistrée.',
                    'action' => 'enter',
                    'reservationId' => $resId,
                    'sessionId' => $sessionId
                ];
            } else {
                // Sortie : trouver la dernière session ouverte pour ce parking et cet utilisateur
                $sessionRepo = $this->sessionRepo;
                $reservationRepo = $this->reservationRepo;
                $invoiceRepo = $this->invoiceRepo;

                $session = $sessionRepo->getOpenSession((int)$dto->parkingId, $userId);

                if (!$session) {
                    return ['success' => false, 'message' => 'Aucune session ouverte trouvée pour cet utilisateur et ce parking.'];
                }

                // Vérifier que la session est liée à une réservation active
                $reservationId = $session['reservation_id'] ?? null;
                if (!$reservationId) {
                    return ['success' => false, 'message' => 'La sortie n est possible que si une réservation en cours a été démarrée.'];
                }

                $r = $reservationRepo->findByIdForUpdate((int)$reservationId);
                if (!$r || ($r['status'] ?? '') !== 'active') {
                    return ['success' => false, 'message' => 'La réservation associée n est pas en cours; impossible de sortir.'];
                }

                // Mettre à jour la session avec exit_time
                $sessionRepo->closeSession((int)$session['id']);

                // Calculer coût / pénalités si une réservation existe
                if ($reservationId) {
                    $reservation = $r;
                    if ($reservation && $reservation['status'] !== 'completed') {
                        // use DB now for overtime calculation as well
                        $dbNowRow3 = $this->pdo->query("SELECT NOW() as now")->fetch(PDO::FETCH_ASSOC);
                        $now = new DateTime($dbNowRow3['now']);
                        $reservationEnd = new DateTime($reservation['end_time']);

                        // Vérifier que l'entrée s'est faite dans la fenêtre de 10 minutes autour du début prévu
                        try {
                            $entryTime = new DateTime($session['entry_time']);
                            $startTime = new DateTime($reservation['start_time']);
                            $diff = abs($entryTime->getTimestamp() - $startTime->getTimestamp());
                            if ($diff > 600) { // plus de 10 minutes
                                return ['success' => false, 'message' => 'Votre entrée n\'est pas dans la fenêtre de réservation de 10 minutes; sortie impossible via l\'application.'];
                            }
                        } catch (Exception $e) {
                            // date parse error during exit checks
                        }

                        // calculer montant de base si non défini
                        $basePrice = (float)$reservation['total_price'];
                        if (!$basePrice || $basePrice <= 0) {
                            // fallback: utiliser hourly_rate * reserved_hours
                            $hourly = $this->parkingRepo->getHourlyRate((int)$reservation['parking_id']);
                            $start = new DateTime($reservation['start_time']);
                            $seconds = max(0, $reservationEnd->getTimestamp() - $start->getTimestamp());
                            $reservedHours = (int) ceil($seconds / 3600);
                            $basePrice = $hourly * max(1, $reservedHours);
                        }

                        $overtimeCost = 0.0;
                        $penalty = 0.0;
                        if ($now > $reservationEnd) {
                            $overtimeSeconds = $now->getTimestamp() - $reservationEnd->getTimestamp();
                            $overtimeHours = (int) ceil($overtimeSeconds / 3600);

                            // récupérer tranches tarifaires
                            $tiers = $this->parkingRepo->getPricingTiers((int)$reservation['parking_id']);

                            $applicablePrice = null;
                            $resEndTimeStr = $reservationEnd->format('H:i:s');
                            foreach ($tiers as $tier) {
                                if ($tier['time'] <= $resEndTimeStr) {
                                    $applicablePrice = (float)$tier['price'];
                                }
                            }
                            if ($applicablePrice === null) {
                                // fallback to parking hourly_rate
                                $applicablePrice = $this->parkingRepo->getHourlyRate((int)$reservation['parking_id']);
                            }

                            $overtimeCost = $applicablePrice * $overtimeHours;
                            $penalty = 20.0 + $overtimeCost; // PENALTY_BASE + overtime
                        }

                        $newTotal = $basePrice + $overtimeCost;

                        // Mettre à jour la réservation
                        $reservationRepo->markCompleted((int)$reservationId, $newTotal, $penalty);

                        // Marquer la session comme overstay si nécessaire
                        if ($overtimeCost > 0) {
                            $sessionRepo->markOverstay((int)$session['id']);
                        }

                        // Créer une facture
                        $invoiceRepo->createInvoice((int)$reservationId, $newTotal + $penalty);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Sortie enregistrée.',
                    'action' => 'exit'
                ];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
