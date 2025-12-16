<?php

require_once __DIR__ . '/../../Application/DTO/RegisterCustomerDTO.php';
require_once __DIR__ . '/../../Application/DTO/AuthenticateUserDTO.php';
// ...ajoute les autres DTOs si besoin

class CustomerController {
    private $registerCustomerUseCase;
    private $authenticateUserUseCase;

    public function __construct($registerCustomerUseCase, $authenticateUserUseCase) {
        $this->registerCustomerUseCase = $registerCustomerUseCase;
        $this->authenticateUserUseCase = $authenticateUserUseCase;
    }

    // Exemple d'inscription
    public function register(array $requestData) {
        $dto = RegisterCustomerDTO::fromArray($requestData);
        $dto->validate();
        return $this->registerCustomerUseCase->execute(
            $dto->name,
            $dto->forename,
            $dto->email,
            $dto->password
        );
    }

    // Exemple d'authentification
    public function authenticate(array $requestData) {
        $dto = AuthenticateUserDTO::fromArray($requestData);
        $dto->validate();
        return $this->authenticateUserUseCase->execute(
            $dto->email,
            $dto->password
        );
    }

    // Ajoute ici d'autres méthodes pour réservation, abonnement, etc.
}
