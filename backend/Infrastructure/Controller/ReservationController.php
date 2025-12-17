<?php

require_once __DIR__ . '/../../Application/DTO/ReserveParkingDTO.php';
require_once __DIR__ . '/../../Domain/Entities/Reservation.php';

class ReservationController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function reserveParking(ReserveParkingDTO $dto): array {
        try {
            // Vérifier que le parking existe
            $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = ?");
            $stmt->execute([$dto->parkingId]);
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            // Vérifier que le client existe
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$dto->customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return ['success' => false, 'message' => 'Client non trouvé.'];
            }

            // Créer la réservation
            $stmt = $this->pdo->prepare(
                "INSERT INTO reservations (user_id, parking_id, start_time, end_time, status) 
                 VALUES (?, ?, ?, ?, 'pending')"
            );
            $stmt->execute([
                $dto->customerId,
                $dto->parkingId,
                $dto->from,
                $dto->to
            ]);

            $reservationId = $this->pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Réservation confirmée.',
                'reservation' => [
                    'id' => $reservationId,
                    'parkingId' => $dto->parkingId,
                    'startTime' => $dto->from,
                    'endTime' => $dto->to
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
