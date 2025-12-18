<?php
class ParkingRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findOwnerById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM parking_owners WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function createParking(int $ownerId, string $name, string $address, string $city, ?float $latitude, ?float $longitude, int $totalSpaces): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO parkings (owner_id, name, address, city, latitude, longitude, total_spaces) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$ownerId, $name, $address, $city, $latitude, $longitude, $totalSpaces]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM parkings WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function getHourlyRate(int $id): float {
        $stmt = $this->pdo->prepare("SELECT hourly_rate FROM parkings WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? (float)$r['hourly_rate'] : 0.0;
    }

    public function getPricingTiers(int $parkingId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM pricing_tiers WHERE parking_id = ? ORDER BY time ASC");
        $stmt->execute([$parkingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
