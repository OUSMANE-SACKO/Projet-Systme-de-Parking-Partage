<?php
    class MySQLParkingRepository implements IParkingRepository {
        private PDO $connection;

        public function __construct(PDO $connection) {
            $this->connection = $connection;
        }

        /**
         * @param int $id
         * @return Parking|null
         */
        public function findById(int $id): ?Parking {
            $stmt = $this->connection->prepare("SELECT * FROM parkings WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return $this->hydrate($row);
        }

        /**
         * @param Parking $parking
         * @return void
         */
        public function save(Parking $parking): void {
            
            $latitude = $parking->getLocation()['latitude'] ?? 0.0;
            $longitude = $parking->getLocation()['longitude'] ?? 0.0;
            
            if ($parking->getId() === null) {
                // INSERT
                $stmt = $this->connection->prepare(
                    "INSERT INTO parkings (latitude, longitude, total_spaces, hourly_rate, owner_id) 
                    VALUES (?, ?, ?, ?, ?)" 
                );
                
                
                $stmt->execute([
                    $latitude,
                    $longitude,
                    $parking->getCapacity(),
                    0.0, // hourly_rate temporaire
                    1    // owner_id temporaire (TODO: Lier au vrai owner)
                ]);

                $newId = (int) $this->connection->lastInsertId();
                $parking->setId($newId);
            } else {
                // UPDATE
                $stmt = $this->connection->prepare(
                    "UPDATE parkings SET latitude = ?, longitude = ?, total_spaces = ? WHERE id = ?"
                );
                $stmt->execute([
                    $latitude, 
                    $longitude, 
                    $parking->getCapacity(), 
                    $parking->getId()
                ]);
            }
        }

        /**
         * @return Parking[]
         */
        public function findAll(): array {
            $stmt = $this->connection->query("SELECT * FROM parkings");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $parkings = [];
            foreach ($rows as $row) {
                $parkings[] = $this->hydrate($row);
            }

            return $parkings;
        }

        /**
         * @param float $latitude
         * @param float $longitude
         * @param float $radiusKm
         * @return Parking[]
         */
        public function findByLocation(float $latitude, float $longitude, float $radiusKm): array {
            $stmt = $this->connection->prepare(
                "SELECT *, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude)))) AS distance 
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

        /**
         * @param int $ownerId
         * @return Parking[]
         */
        public function findByOwnerId(int $ownerId): array {
            $stmt = $this->connection->prepare("SELECT * FROM parkings WHERE owner_id = ?");
            $stmt->execute([$ownerId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $parkings = [];
            foreach ($rows as $row) {
                $parkings[] = $this->hydrate($row);
            }

            return $parkings;
        }

        /**
         * @param array $row
         * @return Parking
         */
        private function hydrate(array $row): Parking {
            // Reconstitution de l'array location depuis les colonnes SQL
            $location = [
                'latitude' => (float)$row['latitude'], 
                'longitude' => (float)$row['longitude']
            ];
            
            // Mapping 'total_spaces' (BDD) vers 'capacity' (Entité)
            $parking = new Parking($location, (int)$row['total_spaces']);
            
            // IMPORTANT : On remplit l'ID !
            $parking->setId((int)$row['id']);
            
            return $parking;
        }
    }
?>