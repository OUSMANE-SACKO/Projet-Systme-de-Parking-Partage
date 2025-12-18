<?php

require_once __DIR__ . '/../../Application/DTO/SubscribeToSubscriptionDTO.php';
require_once __DIR__ . '/../../Infrastructure/Repositories/SubscriptionRepository.php';

class SubscriptionController {
    private PDO $pdo;
    private SubscriptionRepository $subscriptionRepo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->subscriptionRepo = new SubscriptionRepository($pdo);
    }

    public function subscribeToSubscription(SubscribeToSubscriptionDTO $dto): array {
        try {
            // Vérifier que le type d'abonnement existe
            $subType = $this->subscriptionRepo->findTypeById($dto->subscriptionTypeId);
            if (!$subType) {
                return ['success' => false, 'message' => 'Type d\'abonnement non trouvé.'];
            }

            // Vérifier que le client existe
            $customer = $this->subscriptionRepo->findUserById($dto->customerId);
            if (!$customer) {
                return ['success' => false, 'message' => 'Client non trouvé.'];
            }

            // Créer l'abonnement
            $startDate = date('Y-m-d');
            $durationMonths = $subType['duration_months'] ?? 1;
            $endDate = date('Y-m-d', strtotime("+{$durationMonths} months"));

            $subscriptionId = $this->subscriptionRepo->createUserSubscription($dto->customerId, $dto->subscriptionTypeId, $startDate, $endDate, $durationMonths);

            return [
                'success' => true,
                'message' => 'Abonnement souscrit avec succès.',
                'subscription' => [
                    'id' => $subscriptionId,
                    'typeName' => $subType['name'] ?? 'Standard',
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
