<?php
    require_once __DIR__ . '/../Database/Factories/MySQLFactory.php';

    class MySQLUserRepository implements IUserRepository {
        public function findByEmail(string $email): ?User {
            $connection = MySQLFactory::getConnection();
            $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function findById(string $id): ?User {
            $connection = MySQLFactory::getConnection();
            $stmt = $connection->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function existsByEmail(string $email): bool {
            $connection = MySQLFactory::getConnection();
            $stmt = $connection->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        }

        public function save(User $user): void {
            $connection = MySQLFactory::getConnection();
            
            // Déterminer la table cible selon le type d'objet
            $table = ($user instanceof Owner) ? 'parking_owners' : 'users';

            if ($user->getId() === null) {
                // CAS 1 : INSERTION (Nouvel utilisateur)
                // On ne mentionne PAS la colonne 'id', la BDD le fera auto-incrémenter
                $stmt = $connection->prepare(
                    "INSERT INTO {$table} (first_name, last_name, email, password) 
                    VALUES (?, ?, ?, ?)"
                );

                $stmt->execute([
                    $user->getForename(),    // Mapping: forename -> first_name
                    $user->getName(),        // Mapping: name -> last_name
                    $user->getEmail(),
                    $user->getPasswordHash() // Mapping: passwordHash -> password
                ]);

                // CRUCIAL : On récupère l'ID généré par la BDD et on le met dans l'objet
                $newId = (int) $connection->lastInsertId();
                $user->setId($newId);

            } else {
                // CAS 2 : MISE À JOUR (Utilisateur existant avec ID)
                $stmt = $connection->prepare(
                    "UPDATE {$table} 
                    SET first_name = ?, last_name = ?, email = ?, password = ? 
                    WHERE id = ?"
                );

                $stmt->execute([
                    $user->getForename(),
                    $user->getName(),
                    $user->getEmail(),
                    $user->getPasswordHash(),
                    $user->getId()
                ]);
            }
        }

        private function hydrate(array $row): User {
            // Mapper les colonnes de la BDD vers les propriétés de l'entité
            $name = $row['last_name'] ?? '';
            $forename = $row['first_name'] ?? '';
            $email = $row['email'] ?? '';
            $passwordHash = $row['password'] ?? '';
            $id = $row['id'] ?? null;
            
            // Déterminer le type d'utilisateur (par défaut Customer)
            $userType = $row['user_type'] ?? 'customer';
            
            if ($userType === 'owner') {
                $user = new Owner($name, $forename, $email, $passwordHash);
            } else {
                $user = new Customer($name, $forename, $email, $passwordHash);
            }
            
            if ($id !== null) {
                $user->setId((int)$id);
            }
            
            return $user;
        }
    }
?>