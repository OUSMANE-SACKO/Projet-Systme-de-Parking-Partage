<?php
class SubscriptionRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findTypeById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM subscription_types WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function findUserById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function createUserSubscription(int $userId, int $subscriptionTypeId, string $startDate, string $endDate, int $durationMonths): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO user_subscriptions (user_id, subscription_type_id, start_date, end_date, duration_months, status) VALUES (?, ?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$userId, $subscriptionTypeId, $startDate, $endDate, $durationMonths]);
        return (int)$this->pdo->lastInsertId();
    }

    public function createSubscriptionType(int $parkingId, string $name, string $description, float $monthlyPrice, int $durationMonths): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO subscription_types (parking_id, name, description, monthly_price, duration_months) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$parkingId, $name, $description, $monthlyPrice, $durationMonths]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findByParking(int $parkingId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM subscription_types WHERE parking_id = ? ORDER BY monthly_price");
        $stmt->execute([$parkingId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sumActiveMonthlyRevenueForParking(int $parkingId, string $monthEnd, string $monthStart): float {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(st.monthly_price), 0) as revenue FROM user_subscriptions us JOIN subscription_types st ON us.subscription_type_id = st.id WHERE st.parking_id = ? AND us.status = 'active' AND us.start_date <= ? AND us.end_date >= ?"
        );
        $stmt->execute([$parkingId, $monthEnd, $monthStart]);
        return (float)$stmt->fetchColumn();
    }

    public function userHasActiveSubscriptionAt(int $userId, int $parkingId, string $timeStr): bool {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM user_subscriptions us JOIN subscription_types st ON us.subscription_type_id = st.id WHERE st.parking_id = ? AND us.user_id = ? AND us.status = 'active' AND us.start_date <= ? AND us.end_date >= ? LIMIT 1"
        );
        $stmt->execute([$parkingId, $userId, $timeStr, $timeStr]);
        return (bool)$stmt->fetchColumn();
    }

    public function findUserSubscriptions(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT us.*, st.name as type_name, st.description, st.monthly_price, st.duration_months, p.name as parking_name, p.address as parking_address FROM user_subscriptions us JOIN subscription_types st ON us.subscription_type_id = st.id JOIN parkings p ON st.parking_id = p.id WHERE us.user_id = ? ORDER BY us.start_date DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
