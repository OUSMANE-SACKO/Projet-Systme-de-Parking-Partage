<?php

require_once __DIR__ . '/../../Application/DTO/AuthenticateUserDTO.php';
require_once __DIR__ . '/../../Application/UseCases/AuthenticateUserUseCase.php';
require_once __DIR__ . '/../../Domain/Repositories/IUserRepository.php';
require_once __DIR__ . '/../../Domain/Entities/User.php';
require_once __DIR__ . '/../../Domain/Entities/Customer.php';
require_once __DIR__ . '/../../Domain/Entities/Owner.php';
require_once __DIR__ . '/../Repositories/MySQLUserRepository.php';

class AuthController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function authenticateUser(AuthenticateUserDTO $dto): array {
        try {
            $userRepository = new MySQLUserRepository();
            $useCase = new AuthenticateUserUseCase($userRepository);
            
            $result = $useCase->execute($dto->email, $dto->password);

            if ($result['authenticated']) {
                $user = $result['user'];
                // Générer un token simple (en production, utiliser JWT)
                $token = bin2hex(random_bytes(32));
                
                return [
                    'authenticated' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'name' => $user->getName(),
                        'forename' => $user->getForename()
                    ],
                    'message' => 'Connexion réussie.'
                ];
            }

            return [
                'authenticated' => false,
                'message' => $result['message'] ?? 'Identifiants incorrects.'
            ];
        } catch (Exception $e) {
            return [
                'authenticated' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
