<?php

require_once __DIR__ . '/../../Application/DTO/GetParkingsDTO.php';
require_once __DIR__ . '/../../Application/UseCases/SearchAvailableParkingsUseCase.php';
require_once __DIR__ . '/../Repositories/ParkingRepository.php';
require_once __DIR__ . '/../Repositories/SessionRepository.php';

class ParkingListController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getParkings(GetParkingsDTO $dto): array {
        try {
            $useCase = new SearchAvailableParkingsUseCase($this->pdo);
            $result = $useCase->execute($dto);
            
            return [
                'success' => true,
                'parkings' => $result['parkings'] ?? []
            ];
        } catch (Exception $e) {
            error_log('GetParkings Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
