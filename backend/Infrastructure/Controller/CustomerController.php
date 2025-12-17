<?php

require_once __DIR__ . '/../../Application/DTO/RegisterCustomerDTO.php';
require_once __DIR__ . '/../../Application/UseCases/RegisterCustomerUseCase.php';
require_once __DIR__ . '/../../Application/UseCases/HashPasswordUseCase.php';
require_once __DIR__ . '/../../Domain/Repositories/IUserRepository.php';
require_once __DIR__ . '/../../Domain/Entities/User.php';
require_once __DIR__ . '/../../Domain/Entities/Customer.php';
require_once __DIR__ . '/../Repositories/MySQLUserRepository.php';

class CustomerController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registerCustomer(RegisterCustomerDTO $dto): array {
        try {
            $userRepository = new MySQLUserRepository();
            $useCase = new RegisterCustomerUseCase($userRepository);
            
            $customer = $useCase->execute(
                $dto->name,
                $dto->forename,
                $dto->email,
                $dto->password
            );

            return [
                'success' => true,
                'message' => 'Compte créé avec succès.',
                'user' => [
                    'id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'name' => $customer->getName(),
                    'forename' => $customer->getForename()
                ]
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
