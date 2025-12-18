<?php
class ReservationRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function findActiveOrOngoingByParkingAndUser(int $parkingId, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE parking_id = ? AND user_id = ? AND (status = 'active' OR (start_time <= NOW() AND end_time >= NOW())) ORDER BY start_time ASC LIMIT 1");
        $stmt->execute([$parkingId, $userId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function findByUserWithParking(int $userId): array {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, p.name as parking_name, p.address as parking_address
             FROM reservations r
             JOIN parkings p ON r.parking_id = p.id
             WHERE r.user_id = ?
             ORDER BY r.start_time DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForUpdate(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function hasOverlappingReservation(int $userId, int $parkingId, string $from, string $to): bool {
        $overlapStmt = $this->pdo->prepare(
            "SELECT 1 FROM reservations WHERE user_id = ? AND parking_id = ? AND status != 'cancelled' AND NOT (end_time <= ? OR start_time >= ?) LIMIT 1"
        );
        $overlapStmt->execute([$userId, $parkingId, $from, $to]);
        return (bool)$overlapStmt->fetchColumn();
    }

    public function createReservation(int $userId, int $parkingId, string $from, string $to): int {
        $stmt = $this->pdo->prepare(
            "INSERT INTO reservations (user_id, parking_id, start_time, end_time, status) VALUES (?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$userId, $parkingId, $from, $to]);
        return (int)$this->pdo->lastInsertId();
    }

    public function markCompleted(int $id, float $totalPrice, float $penalty): void {
        $u = $this->pdo->prepare("UPDATE reservations SET total_price = ?, penalty_amount = ?, status = 'completed' WHERE id = ?");
        $u->execute([$totalPrice, $penalty, $id]);
    }

    public function deleteById(int $id): void {
        $d = $this->pdo->prepare("DELETE FROM reservations WHERE id = ?");
        $d->execute([$id]);
    }

    public function findByParking(int $parkingId, ?string $status = null): array {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.email FROM reservations r JOIN users u ON r.user_id = u.id WHERE r.parking_id = ?";
        $params = [$parkingId];
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.start_time DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActiveReservationsAt(int $parkingId, string $timeStr): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM reservations WHERE parking_id = ? AND status IN ('pending','active') AND start_time <= ? AND end_time >= ?"
        );
        $stmt->execute([$parkingId, $timeStr, $timeStr]);
        return (int)$stmt->fetchColumn();
    }

    public function sumCompletedRevenueForPeriod(int $parkingId, string $start, string $end): float {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(total_price), 0) as revenue FROM reservations WHERE parking_id = ? AND status = 'completed' AND end_time >= ? AND end_time <= ?"
        );
        $stmt->execute([$parkingId, $start, $end]);
        return (float)$stmt->fetchColumn();
    }

    public function userHasActiveReservationAt(int $userId, int $parkingId, string $timeStr): bool {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM reservations WHERE user_id = ? AND parking_id = ? AND start_time <= ? AND end_time >= ? LIMIT 1"
        );
        $stmt->execute([$userId, $parkingId, $timeStr, $timeStr]);
        return (bool)$stmt->fetchColumn();
    }

    public function findByIdWithDetails(int $id): ?array {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, p.name as parking_name, p.address as parking_address, p.hourly_rate, u.first_name, u.last_name, u.email FROM reservations r JOIN parkings p ON r.parking_id = p.id JOIN users u ON r.user_id = u.id WHERE r.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
}
