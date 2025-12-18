<?php
require_once __DIR__ . '/../DTO/EnterExitParkingDTO.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/ParkingRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/ReservationRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/SessionRepository.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/InvoiceRepository.php';

class ExitParkingUseCase {
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
        $userId = $dto->userId ?? null;
        $session = $this->sessionRepo->getOpenSession((int)$dto->parkingId, $userId);
        if (!$session) {
            return ['success' => false, 'message' => 'Aucune session ouverte trouvée pour cet utilisateur et ce parking.'];
        }
        $reservationId = $session['reservation_id'] ?? null;
        if (!$reservationId) {
            return ['success' => false, 'message' => 'La sortie n est possible que si une réservation en cours a été démarrée.'];
        }
        $r = $this->reservationRepo->findByIdForUpdate((int)$reservationId);
        if (!$r || ($r['status'] ?? '') !== 'active') {
            return ['success' => false, 'message' => 'La réservation associée n est pas en cours; impossible de sortir.'];
        }
        $this->sessionRepo->closeSession((int)$session['id']);
        if ($reservationId) {
            $reservation = $r;
            if ($reservation && $reservation['status'] !== 'completed') {
                $dbNowRow3 = $this->pdo->query("SELECT NOW() as now")->fetch(PDO::FETCH_ASSOC);
                $now = new DateTime($dbNowRow3['now']);
                $reservationEnd = new DateTime($reservation['end_time']);
                try {
                    $entryTime = new DateTime($session['entry_time']);
                    $startTime = new DateTime($reservation['start_time']);
                    $diff = abs($entryTime->getTimestamp() - $startTime->getTimestamp());
                    if ($diff > 600) {
                        return ['success' => false, 'message' => 'Votre entrée n\'est pas dans la fenêtre de réservation de 10 minutes; sortie impossible via l\'application.'];
                    }
                } catch (Exception $e) {}
                $basePrice = (float)$reservation['total_price'];
                if (!$basePrice || $basePrice <= 0) {
                    $hourly = $this->parkingRepo->getHourlyRate((int)$reservation['parking_id']);
                    $start = new DateTime($reservation['start_time']);
                    $seconds = max(0, $reservationEnd->getTimestamp() - $start->getTimestamp());
                    $reservedHours = (int) ceil($seconds / 3600);
                    $basePrice = $hourly * max(1, $reservedHours);
                }
                $overtimeCost = 0.0;
                $penalty = 0.0;
                if ($now > $reservationEnd) {
                    $overtimeSeconds = $now->getTimestamp() - $reservationEnd->getTimestamp();
                    $overtimeHours = (int) ceil($overtimeSeconds / 3600);
                    $tiers = $this->parkingRepo->getPricingTiers((int)$reservation['parking_id']);
                    $applicablePrice = null;
                    $resEndTimeStr = $reservationEnd->format('H:i:s');
                    foreach ($tiers as $tier) {
                        if ($tier['time'] <= $resEndTimeStr) {
                            $applicablePrice = (float)$tier['price'];
                        }
                    }
                    if ($applicablePrice === null) {
                        $applicablePrice = $this->parkingRepo->getHourlyRate((int)$reservation['parking_id']);
                    }
                    $overtimeCost = $applicablePrice * $overtimeHours;
                    $penalty = 20.0 + $overtimeCost;
                }
                $newTotal = $basePrice + $overtimeCost;
                $this->reservationRepo->markCompleted((int)$reservationId, $newTotal, $penalty);
                if ($overtimeCost > 0) {
                    $this->sessionRepo->markOverstay((int)$session['id']);
                }
                $this->invoiceRepo->createInvoice((int)$reservationId, $newTotal + $penalty);
            }
        }
        return [
            'success' => true,
            'message' => 'Sortie enregistrée.',
            'action' => 'exit'
        ];
    }
}