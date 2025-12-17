<?php

require_once __DIR__ . '/../../Application/DTO/EnterExitParkingDTO.php';

class ParkingSpaceController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function enterExitParking(EnterExitParkingDTO $dto): array {
        try {
            // Vérifier que le parking existe
            $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = ?");
            $stmt->execute([$dto->parkingId]);
            $parking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            if ($dto->action === 'enter') {
                // Enregistrer l'entrée dans parkings_sessions
                $stmt = $this->pdo->prepare(
                    "INSERT INTO parkings_sessions (parking_id, user_id, entry_time) 
                     VALUES (?, 0, NOW())"
                );
                $stmt->execute([$dto->parkingId]);

                return [
                    'success' => true,
                    'message' => 'Entrée enregistrée.',
                    'action' => 'enter',
                    'vehiclePlate' => $dto->vehiclePlate
                ];
            } else {
                // Enregistrer la sortie
                $stmt = $this->pdo->prepare(
                    "UPDATE parkings_sessions 
                     SET exit_time = NOW() 
                     WHERE parking_id = ? AND exit_time IS NULL 
                     ORDER BY entry_time DESC LIMIT 1"
                );
                $stmt->execute([$dto->parkingId]);

                return [
                    'success' => true,
                    'message' => 'Sortie enregistrée.',
                    'action' => 'exit',
                    'vehiclePlate' => $dto->vehiclePlate
                ];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
