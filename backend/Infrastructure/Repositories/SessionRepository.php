<?php
class SessionRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getOpenSession(int $parkingId, int $userId): ?array {
        $stmt = $this->pdo->prepare("SELECT id, entry_time, reservation_id FROM parkings_sessions WHERE parking_id = ? AND user_id = ? AND exit_time IS NULL ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$parkingId, $userId]);
        $s = $stmt->fetch(PDO::FETCH_ASSOC);
        return $s ?: null;
    }

    public function getOpenSessionByReservation(int $reservationId): ?array {
        $stmt = $this->pdo->prepare("SELECT id, entry_time, reservation_id FROM parkings_sessions WHERE reservation_id = ? AND exit_time IS NULL ORDER BY entry_time DESC LIMIT 1");
        $stmt->execute([$reservationId]);
        $s = $stmt->fetch(PDO::FETCH_ASSOC);
        return $s ?: null;
    }

    public function createSession(int $parkingId, int $userId, ?int $reservationId): int {
        $stmt = $this->pdo->prepare("INSERT INTO parkings_sessions (parking_id, user_id, reservation_id, entry_time) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$parkingId, $userId, $reservationId]);
        return (int)$this->pdo->lastInsertId();
    }

    public function closeSession(int $sessionId): void {
        $u = $this->pdo->prepare("UPDATE parkings_sessions SET exit_time = NOW() WHERE id = ?");
        $u->execute([$sessionId]);
    }

    public function markOverstay(int $sessionId): void {
        $u = $this->pdo->prepare("UPDATE parkings_sessions SET is_overstay = 1 WHERE id = ?");
        $u->execute([$sessionId]);
    }

    public function detachReservationFromSessions(int $reservationId): void {
        $u = $this->pdo->prepare("UPDATE parkings_sessions SET reservation_id = NULL WHERE reservation_id = ?");
        $u->execute([$reservationId]);
    }

    public function findByParking(int $parkingId, bool $activeOnly = false): array {
        $sql = "SELECT ps.*, u.first_name, u.last_name, u.email FROM parkings_sessions ps LEFT JOIN users u ON ps.user_id = u.id WHERE ps.parking_id = ?";
        $params = [$parkingId];
        if ($activeOnly) {
            $sql .= " AND ps.exit_time IS NULL";
        }
        $sql .= " ORDER BY ps.entry_time DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActiveSessions(int $parkingId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM parkings_sessions WHERE parking_id = ? AND exit_time IS NULL");
        $stmt->execute([$parkingId]);
        return (int)$stmt->fetchColumn();
    }

    public function findByUser(int $userId): array {
        $sql = "SELECT ps.*, p.name as parking_name, p.address as parking_address FROM parkings_sessions ps JOIN parkings p ON ps.parking_id = p.id WHERE ps.user_id = ? ORDER BY ps.entry_time DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
