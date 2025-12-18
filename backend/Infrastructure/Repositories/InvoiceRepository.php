<?php
class InvoiceRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createInvoice(int $reservationId, float $amount): int {
        $stmt = $this->pdo->prepare("INSERT INTO invoices (reservation_id, amount) VALUES (?, ?)");
        $stmt->execute([$reservationId, $amount]);
        return (int)$this->pdo->lastInsertId();
    }
}
