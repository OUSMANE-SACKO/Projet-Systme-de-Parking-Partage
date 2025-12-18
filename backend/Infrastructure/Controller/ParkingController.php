<?php

require_once __DIR__ . '/../../Application/DTO/AddParkingDTO.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/ParkingRepository.php';

class ParkingController {
    private PDO $pdo;
    private ParkingRepository $parkingRepo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->parkingRepo = new ParkingRepository($pdo);
    }

    public function addParking(AddParkingDTO $dto): array {
        try {
            // Vérifier que le propriétaire existe
            $owner = $this->parkingRepo->findOwnerById($dto->ownerId);
            if (!$owner) {
                return ['success' => false, 'message' => 'Propriétaire non trouvé.'];
            }

            // Créer le parking via le repository
            $parkingId = $this->parkingRepo->createParking(
                $dto->ownerId,
                $dto->name,
                $dto->address,
                $dto->city,
                $dto->latitude,
                $dto->longitude,
                $dto->totalSpaces
            );

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
