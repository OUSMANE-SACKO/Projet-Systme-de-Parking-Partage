<?php

require_once __DIR__ . '/../../Application/DTO/RegisterOwnerDTO.php';
require_once __DIR__ . '/../../Application/UseCases/RegisterOwnerUseCase.php';

class OwnerController {
    private RegisterOwnerUseCase $registerOwnerUseCase;

    public function __construct(RegisterOwnerUseCase $registerOwnerUseCase) {
        $this->registerOwnerUseCase = $registerOwnerUseCase;
    }

    public function registerOwner(RegisterOwnerDTO $dto): array {
        try {
            return $this->registerOwnerUseCase->execute($dto);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
