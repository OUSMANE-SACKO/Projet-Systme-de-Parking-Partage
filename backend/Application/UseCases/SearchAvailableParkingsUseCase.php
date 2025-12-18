<?php
    class SearchAvailableParkingsUseCase {
            private $parkingRepo;
            private $sessionRepo;
            private $pdo;
            public function __construct($pdo) {
                $this->pdo = $pdo;
                $this->parkingRepo = new ParkingRepository($pdo);
                $this->sessionRepo = new SessionRepository($pdo);
            }
            public function execute(GetParkingsDTO $dto): array {
                $sql = "SELECT id, name, address, city, latitude, longitude, total_spaces, hourly_rate FROM parkings";
                $params = [];
                $conditions = [];
                if ($dto->city) {
                    $conditions[] = "city LIKE ?";
                    $params[] = '%' . $dto->city . '%';
                }
                if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(" AND ", $conditions);
                }
                $sql .= " ORDER BY city, name LIMIT 100";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $parkings = [];
                foreach ($rows as $row) {
                    $occupied = $this->sessionRepo->countActiveSessions((int)$row['id']);
                    $available = max(0, (int)$row['total_spaces'] - $occupied);
                    $parkings[] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'address' => $row['address'],
                        'city' => $row['city'],
                        'lat' => (float)$row['latitude'],
                        'lng' => (float)$row['longitude'],
                        'totalSpaces' => (int)$row['total_spaces'],
                        'availableSpaces' => $available,
                        'price' => (float)$row['hourly_rate']
                    ];
                }
                return [
                    'parkings' => $parkings,
                    'count' => count($parkings)
                ];
            }
            
        /**
         * @param float $lat1 Latitude point 1
         * @param float $lon1 Longitude point 1
         * @param float $lat2 Latitude point 2
         * @param float $lon2 Longitude point 2
         * @return float Distance en kilomètres
         */
        private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
            $earthRadius = 6371; // Rayon de la Terre en km

            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            return $earthRadius * $c;
        }
    }
?>