<?php
    class MySQLUserRepository implements IUserRepository {
        private DatabaseManager $db;

        public function __construct(DatabaseManager $db) {
            $this->db = $db;
        }

        public function findByEmail(string $email): ?User {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function findById(string $id): ?User {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function existsByEmail(string $email): bool {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetchColumn() > 0;
        }

        public function save(User $user): void {
            $connection = $this->db->getConnection();
            
            $stmt = $connection->prepare(
                "INSERT INTO users (id, name, forename, email, password_hash, user_type) 
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 name = VALUES(name), 
                 forename = VALUES(forename), 
                 email = VALUES(email), 
                 password_hash = VALUES(password_hash)"
            );

            $userType = $user instanceof Customer ? 'customer' : 'owner';

            $stmt->execute([
                $user->getId(),
                $user->getName(),
                $user->getForename(),
                $user->getEmail(),
                $user->getPasswordHash(),
                $userType
            ]);
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