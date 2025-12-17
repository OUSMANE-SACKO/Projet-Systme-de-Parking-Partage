<?php

require_once __DIR__ . '/../../Application/DTO/AddParkingDTO.php';

class ParkingController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addParking(AddParkingDTO $dto): array {
        try {
            // Vérifier que le propriétaire existe
            $stmt = $this->pdo->prepare("SELECT * FROM parking_owners WHERE id = ?");
            $stmt->execute([$dto->ownerId]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$owner) {
                return ['success' => false, 'message' => 'Propriétaire non trouvé.'];
            }

            // Créer le parking
            $stmt = $this->pdo->prepare(
                "INSERT INTO parkings (owner_id, name, address, city, latitude, longitude, total_spaces) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $dto->ownerId,
                $dto->name,
                $dto->address,
                $dto->city,
                $dto->latitude,
                $dto->longitude,
                $dto->totalSpaces
            ]);

            $parkingId = $this->pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Parking ajouté avec succès.',
                'parking' => [
                    'id' => $parkingId,
                    'name' => $dto->name,
                    'address' => $dto->address,
                    'city' => $dto->city
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
