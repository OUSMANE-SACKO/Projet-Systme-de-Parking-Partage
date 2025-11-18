<?php
    class MySQLParkingRepository implements IParkingRepository {
        private DatabaseManager $db;

        public function __construct(DatabaseManager $db) {
            $this->db = $db;
        }

        public function findById(string $id): ?Parking {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM parkings WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        public function save(Parking $parking): void {
            $connection = $this->db->getConnection();
            
            $locationJson = json_encode($parking->getLocation());
            
            $stmt = $connection->prepare(
                "INSERT INTO parkings (id, location, capacity) 
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 location = VALUES(location), 
                 capacity = VALUES(capacity)"
            );

            $stmt->execute([
                $parking->getId(),
                $locationJson,
                $parking->getCapacity()
            ]);
        }

        public function findAll(): array {
            $connection = $this->db->getConnection();
            $stmt = $connection->query("SELECT * FROM parkings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $parkings = [];
            foreach ($rows as $row) {
                $parkings[] = $this->hydrate($row);
            }

            return $parkings;
        }

        public function findByLocation(float $latitude, float $longitude, float $radiusKm): array {
            $connection = $this->db->getConnection();
            
            $stmt = $connection->prepare(
                "SELECT *, 
                 (6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(location, '$.latitude'))) * 
                 cos(radians(JSON_EXTRACT(location, '$.longitude')) - radians(?)) + 
                 sin(radians(?)) * sin(radians(JSON_EXTRACT(location, '$.latitude'))))) AS distance 
                 FROM parkings 
                 HAVING distance <= ? 
                 ORDER BY distance"
            );

            $stmt->execute([$latitude, $longitude, $latitude, $radiusKm]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $parkings = [];
            foreach ($rows as $row) {
                $parkings[] = $this->hydrate($row);
            }

            return $parkings;
        }

        public function findByOwnerId(string $ownerId): array {
            $connection = $this->db->getConnection();
            $stmt = $connection->prepare("SELECT * FROM parkings WHERE owner_id = ?");
            $stmt->execute([$ownerId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $parkings = [];
            foreach ($rows as $row) {
                $parkings[] = $this->hydrate($row);
            }

            return $parkings;
        }

        private function hydrate(array $row): Parking {
            $location = json_decode($row['location'], true);
            return new Parking($location, (int)$row['capacity']);
        }
    }
?>