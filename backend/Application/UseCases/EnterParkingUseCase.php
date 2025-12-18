<?php
require_once __DIR__ . '/../DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/ParkingRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/ReservationRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/SessionRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/InvoiceRepository.php';

class EnterParkingUseCase {
    private $parkingRepo;
    private $reservationRepo;
    private $sessionRepo;
    private $invoiceRepo;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->parkingRepo = new ParkingRepository($pdo);
        $this->reservationRepo = new ReservationRepository($pdo);
        $this->sessionRepo = new SessionRepository($pdo);
        $this->invoiceRepo = new InvoiceRepository($pdo);
    }

    public function execute(EnterExitParkingDTO $dto): array {
        // Vérifier que le parking existe
        $parking = $this->parkingRepo->findById((int)$dto->parkingId);
        if (!$parking) {
            return ['success' => false, 'message' => 'Parking non trouvé.'];
        }
        $userId = $dto->userId ?? null;
        if (!$userId || !is_int($userId) || $userId <= 0) {
            return ['success' => false, 'message' => 'Utilisateur non authentifié.'];
        }
        // Use the database server time for comparisons to match reservation timestamps
        $dbNowRow = $this->pdo->query("SELECT NOW() as now")->fetch(PDO::FETCH_ASSOC);
        $now = new DateTime($dbNowRow['now']);
        $graceMinutes = 10;
        // Find reservation (status active OR currently in time window)
        $reservation = $this->reservationRepo->findActiveOrOngoingByParkingAndUser((int)$dto->parkingId, $userId);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Aucune réservation en cours. Vous ne pouvez pas entrer.'];
        }
        // Vérifier qu'il n'existe pas déjà une session ouverte pour cet utilisateur et ce parking
        $existing = $this->sessionRepo->getOpenSession((int)$dto->parkingId, $userId);
        if ($existing) {
            return ['success' => false, 'message' => 'Vous avez déjà une session ouverte pour ce parking.'];
        }
        // Enregistrer l'entrée dans parkings_sessions (associée à la réservation trouvée)
        $resId = isset($reservation['id']) ? (int)$reservation['id'] : null;
        $sessionId = $this->sessionRepo->createSession((int)$dto->parkingId, $userId, $resId);
        return [
            'success' => true,
            'message' => 'Entrée enregistrée.',
            'action' => 'enter',
            'reservationId' => $resId,
            'sessionId' => $sessionId
        ];
    }
}