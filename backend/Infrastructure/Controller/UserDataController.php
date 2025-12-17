<?php

require_once __DIR__ . '/../../Application/DTO/GetUserReservationsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetUserSessionsDTO.php';
require_once __DIR__ . '/../../Application/DTO/GetReservationInvoiceDTO.php';

class UserDataController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getUserReservations(GetUserReservationsDTO $dto): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT r.*, p.name as parking_name, p.address as parking_address
                FROM reservations r
                JOIN parkings p ON r.parking_id = p.id
                WHERE r.user_id = ?
                ORDER BY r.start_time DESC
            ");
            $stmt->execute([$dto->userId]);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'reservations' => array_map(function($r) {
                    $now = new DateTime();
                    $start = new DateTime($r['start_time']);
                    $end = new DateTime($r['end_time']);
                    $status = $r['status'];
                    if ($status === 'pending' && $start <= $now && $end >= $now) $status = 'active';
                    elseif ($status === 'pending' && $end < $now) $status = 'past';
                    
                    return [
                        'id' => $r['id'],
                        'parkingId' => $r['parking_id'],
                        'parkingName' => $r['parking_name'],
                        'parkingAddress' => $r['parking_address'],
                        'startTime' => $r['start_time'],
                        'endTime' => $r['end_time'],
                        'totalPrice' => $r['total_price'],
                        'status' => $status
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
            $stmt = $this->pdo->prepare("
                SELECT ps.*, p.name as parking_name, p.address as parking_address
                FROM parkings_sessions ps
                JOIN parkings p ON ps.parking_id = p.id
                WHERE ps.user_id = ?
                ORDER BY ps.entry_time DESC
            ");
            $stmt->execute([$dto->userId]);
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $stmt = $this->pdo->prepare("
                SELECT r.*, p.name as parking_name, p.address as parking_address, p.hourly_rate,
                       u.first_name, u.last_name, u.email
                FROM reservations r
                JOIN parkings p ON r.parking_id = p.id
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$dto->reservationId]);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                return ['success' => false, 'message' => 'Réservation non trouvée.'];
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
}
