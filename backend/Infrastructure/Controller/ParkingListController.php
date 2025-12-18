<?php

require_once __DIR__ . '/../../Application/DTO/GetParkingsDTO.php';
require_once __DIR__ . '/../../Application/UseCases/SearchAvailableParkingsUseCase.php';

class ParkingListController {
    private SearchAvailableParkingsUseCase $searchAvailableParkingsUseCase;

    public function __construct(SearchAvailableParkingsUseCase $searchAvailableParkingsUseCase) {
        $this->searchAvailableParkingsUseCase = $searchAvailableParkingsUseCase;
    }

    public function getParkings(GetParkingsDTO $dto): array {
        try {
            return $this->searchAvailableParkingsUseCase->execute($dto);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
