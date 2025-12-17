<?php

require_once __DIR__ . '/../../Application/DTO/GetParkingsDTO.php';

class ParkingListController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getParkings(GetParkingsDTO $dto): array {
        try {
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

            $parkings = array_map(function($row) {
                return [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'address' => $row['address'],
                    'city' => $row['city'],
                    'lat' => (float)$row['latitude'],
                    'lng' => (float)$row['longitude'],
                    'totalSpaces' => (int)$row['total_spaces'],
                    'price' => (float)$row['hourly_rate']
                ];
            }, $rows);

            return [
                'success' => true,
                'parkings' => $parkings,
                'count' => count($parkings)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
