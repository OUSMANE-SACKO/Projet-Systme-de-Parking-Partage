<?php

require_once __DIR__ . '/../../Application/DTO/ReserveParkingDTO.php';
require_once __DIR__ . '/../../Application/DTO/CancelReservationDTO.php';
require_once __DIR__ . '/../../Domain/Entities/Reservation.php';
require_once __DIR__ . '/../..//Infrastructure/Repositories/ReservationRepository.php';
require_once __DIR__ . '/../..//Infrastructure/Repositories/SessionRepository.php';
require_once __DIR__ . '/../..//Infrastructure/Repositories/ParkingRepository.php';
require_once __DIR__ . '/../..//Infrastructure/Repositories/SubscriptionRepository.php';

class ReservationController {
    private PDO $pdo;
    private ReservationRepository $reservationRepo;
    private SessionRepository $sessionRepo;
    private ParkingRepository $parkingRepo;
    private SubscriptionRepository $subscriptionRepo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->reservationRepo = new ReservationRepository($pdo);
        $this->sessionRepo = new SessionRepository($pdo);
        $this->parkingRepo = new ParkingRepository($pdo);
        $this->subscriptionRepo = new SubscriptionRepository($pdo);
    }

    public function reserveParking(ReserveParkingDTO $dto): array {
        try {
            // Vérifier que le parking existe
            $parking = $this->parkingRepo->findById((int)$dto->parkingId);
            if (!$parking) {
                return ['success' => false, 'message' => 'Parking non trouvé.'];
            }

            // Vérifier que le client existe
            $customer = $this->subscriptionRepo->findUserById($dto->customerId);
            if (!$customer) {
                return ['success' => false, 'message' => 'Client non trouvé.'];
            }

            // Vérifier la durée minimale (>= 5 minutes)
            $start = new DateTime($dto->from);
            $end = new DateTime($dto->to);
            $diffSeconds = $end->getTimestamp() - $start->getTimestamp();
            if ($diffSeconds < 300) { // moins de 5 minutes
                return ['success' => false, 'message' => 'La durée minimale de réservation est de 5 minutes.'];
            }

            // Vérifier qu'il n'existe pas déjà une réservation pour ce user/parking qui chevauche
            if ($this->reservationRepo->hasOverlappingReservation($dto->customerId, $dto->parkingId, $dto->from, $dto->to)) {
                return ['success' => false, 'message' => 'Vous avez déjà une réservation qui chevauche cette période pour ce parking.'];
            }

            // Créer la réservation via le repository
            $reservationId = $this->reservationRepo->createReservation($dto->customerId, $dto->parkingId, $dto->from, $dto->to);

            return [
                'success' => true,
                'message' => 'Réservation confirmée.',
                'reservation' => [
                    'id' => $reservationId,
                    'parkingId' => $dto->parkingId,
                    'startTime' => $dto->from,
                    'endTime' => $dto->to
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function cancelReservation(CancelReservationDTO $dto): array {
        try {
            // Vérifier que la réservation existe et appartient à l'utilisateur
            $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ? LIMIT 1");
            $stmt->execute([$dto->reservationId, $dto->userId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$res) return ['success' => false, 'message' => 'Réservation introuvable.'];

            // Ne pas annuler si déjà en cours
            if ($res['status'] === 'active') return ['success' => false, 'message' => 'Impossible d\'annuler une réservation en cours.'];

            // Démarrer une transaction pour nettoyer d'abord les sessions liées, puis supprimer la réservation
            $this->pdo->beginTransaction();
            try {
                // Détacher la réservation des sessions si nécessaire
                $this->sessionRepo->detachReservationFromSessions($dto->reservationId);

                // Supprimer la réservation via repository
                $this->reservationRepo->deleteById($dto->reservationId);

                $this->pdo->commit();
                return ['success' => true, 'message' => 'Réservation supprimée.', 'reservation' => ['id' => $dto->reservationId]];
            } catch (Exception $e) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Erreur lors de la suppression de la réservation.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
