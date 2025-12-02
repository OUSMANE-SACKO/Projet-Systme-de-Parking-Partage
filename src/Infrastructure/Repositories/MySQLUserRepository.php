<?php
    class MySQLUserRepository implements IUserRepository {
        private PDO $connection;

        public function __construct(PDO $connection) {
            $this->connection = $connection;
        }

        public function findByEmail(string $email): ?User {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function findById(string $id): ?User {
            $stmt = $this->connection->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function existsByEmail(string $email): bool {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        }

        public function findAll(): array {
            $stmt = $this->connection->query("SELECT * FROM users");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $users = [];
            foreach ($rows as $row) {
                $users[] = $this->hydrate($row);
            }

            return $users;
        }

        public function save(User $user): void {            
            // Déterminer la table cible selon le type d'objet
            $table = ($user instanceof Owner) ? 'parking_owners' : 'users';

            if ($user->getId() === null) {
                // CAS 1 : INSERTION (Nouvel utilisateur)
                // On ne mentionne PAS la colonne 'id', la BDD le fera auto-incrémenter
                $stmt = $this->connection->prepare(
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
                $newId = (int) $this->connection->lastInsertId();
                $user->setId($newId);

            } else {
                // CAS 2 : MISE À JOUR (Utilisateur existant avec ID)
                $stmt = $this->connection->prepare(
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
            if ($row['user_type'] === 'customer') {
                return new Customer($row['name'], $row['forename'], $row['email'], $row['password_hash']);
            } else {
                return new Owner($row['name'], $row['forename'], $row['email'], $row['password_hash']);
            }
        }
    }
?>