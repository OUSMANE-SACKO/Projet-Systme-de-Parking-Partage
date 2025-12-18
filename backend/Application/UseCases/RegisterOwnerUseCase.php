<?php
    class RegisterOwnerUseCase {
            private $pdo;

            public function __construct($pdo) {
                $this->pdo = $pdo;
        }

        /**
         * @param string
         * @param string
         * @param string
         * @param string
         * @return Owner
         */
            public function execute(RegisterOwnerDTO $dto): array {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format.');
            }

            if ($this->userRepository->existsByEmail($email)) {
                throw new InvalidArgumentException('Email already registered.');
            }

            if (strlen($password) < 8) {
                throw new InvalidArgumentException('Password must be at least 8 characters long.');
            }

            $hashPasswordUseCase = new HashPasswordUseCase();
            $passwordHash = $hashPasswordUseCase->execute($password);

            $owner = new Owner($name, $forename, $email, $passwordHash);
            $this->userRepository->save($owner);

            return $owner;
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
        }
    }
?>