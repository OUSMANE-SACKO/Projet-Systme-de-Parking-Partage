<?php

require_once __DIR__ . '/../../Application/DTO/GetParkingsDTO.php';
require_once __DIR__ . '/../../Application/UseCases/GetParkingSpacesUseCase.php';
require_once __DIR__ . '/../../Domain/Repositories/IParkingRepository.php';
require_once __DIR__ . '/../../Domain/Entities/Parking.php';
require_once __DIR__ . '/../../Domain/Entities/ParkingSpace.php';
require_once __DIR__ . '/../Repositories/MySQLParkingRepository.php';

class ParkingListController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getParkings(GetParkingsDTO $dto): array {
        try {
            $parkingRepository = new MySQLParkingRepository($this->pdo);
            $useCase = new GetParkingSpacesUseCase($parkingRepository);
            
            $parkings = $useCase->execute();
            
            return [
                'success' => true,
                'parkings' => $parkings
            ];
        } catch (Exception $e) {
            error_log('GetParkings Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
