<?php

require_once __DIR__ . '/../../Application/DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../Repositories/ReservationRepository.php';
require_once __DIR__ . '/../Repositories/SessionRepository.php';
require_once __DIR__ . '/../Repositories/InvoiceRepository.php';
require_once __DIR__ . '/../Repositories/ParkingRepository.php';

require_once __DIR__ . '/../../Application/UseCases/EnterParkingUseCase.php';
require_once __DIR__ . '/../../Application/UseCases/ExitParkingUseCase.php';
require_once __DIR__ . '/../../Application/DTO/EnterExitParkingDTO.php';

class ParkingSpaceController {
    private EnterParkingUseCase $enterParkingUseCase;
    private ExitParkingUseCase $exitParkingUseCase;

    public function __construct(EnterParkingUseCase $enterParkingUseCase, ExitParkingUseCase $exitParkingUseCase) {
        $this->enterParkingUseCase = $enterParkingUseCase;
        $this->exitParkingUseCase = $exitParkingUseCase;
    }

    public function enterExitParking(EnterExitParkingDTO $dto): array {
        try {
            $dto->validate();
            if ($dto->action === 'enter') {
                $result = $this->enterParkingUseCase->execute($dto);
            } else {
                $result = $this->exitParkingUseCase->execute($dto);
            }
            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
