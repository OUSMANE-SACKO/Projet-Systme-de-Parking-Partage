<?php

require_once __DIR__ . '/../../Application/DTO/RegisterOwnerDTO.php';

class OwnerController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function registerOwner(RegisterOwnerDTO $dto): array {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $this->pdo->prepare("SELECT id FROM parking_owners WHERE email = ?");
            $stmt->execute([$dto->email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé.'];
            }

            // Hasher le mot de passe
            $hashedPassword = password_hash($dto->password, PASSWORD_ARGON2ID);

            // Insérer le propriétaire dans parking_owners
            $stmt = $this->pdo->prepare(
                "INSERT INTO parking_owners (first_name, last_name, email, password) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$dto->forename, $dto->name, $dto->email, $hashedPassword]);

            $ownerId = $this->pdo->lastInsertId();

            // Insérer aussi dans users avec le rôle 'owner'
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'owner')"
            );
            $stmt->execute([$dto->forename, $dto->name, $dto->email, $hashedPassword]);

            $userId = $this->pdo->lastInsertId();

            return [
                'success' => true,
                'message' => 'Compte propriétaire créé avec succès.',
                'owner' => [
                    'id' => $ownerId,
                    'user_id' => $userId,
                    'email' => $dto->email,
                    'name' => $dto->name,
                    'forename' => $dto->forename
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
