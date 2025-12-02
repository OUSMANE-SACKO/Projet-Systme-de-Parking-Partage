<?php

require_once __DIR__ . '/../../Application/DTO/RegisterCustomerDTO.php';
require_once __DIR__ . '/../../Application/DTO/AuthenticateUserDTO.php';
require_once __DIR__ . '/../../Application/DTO/ReserveParkingDTO.php';
require_once __DIR__ . '/../../Application/DTO/AddParkingSubscriptionDTO.php';
require_once __DIR__ . '/../../Application/DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../../Application/UseCase/SubscribeToSubscriptionDTO.php';

class CustomerController {
    private $registerCustomerUseCase;
    private $authenticateUserUseCase;

    public function __construct($registerCustomerUseCase, $authenticateUserUseCase) {
        $this->registerCustomerUseCase = $registerCustomerUseCase;
        $this->authenticateUserUseCase = $authenticateUserUseCase;
    }

    // Inscription
    public function register(array $requestData) {
        $dto = RegisterCustomerDTO::fromArray($requestData);
        $dto->validate();
        return $this->registerCustomerUseCase->execute($dto);
    }

    // Authentification
    public function authenticate(array $requestData) {
        $dto = AuthenticateUserDTO::fromArray($requestData);
        $dto->validate();
        return $this->authenticateUserUseCase->execute($dto);
    }

    // Ajoute ici d'autres méthodes pour réservation, abonnement, etc.
}
