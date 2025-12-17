<?php

require_once __DIR__ . '/../../Application/DTO/SubscribeToSubscriptionDTO.php';

class SubscriptionController {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function subscribeToSubscription(SubscribeToSubscriptionDTO $dto): array {
        try {
            // Vérifier que le type d'abonnement existe
            $stmt = $this->pdo->prepare("SELECT * FROM subscription_types WHERE id = ?");
            $stmt->execute([$dto->subscriptionTypeId]);
            $subType = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$subType) {
                return ['success' => false, 'message' => 'Type d\'abonnement non trouvé.'];
            }

            // Vérifier que le client existe
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$dto->customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$customer) {
                return ['success' => false, 'message' => 'Client non trouvé.'];
            }

            // Créer l'abonnement
            $startDate = date('Y-m-d');
            $durationMonths = $subType['duration_months'] ?? 1;
            $endDate = date('Y-m-d', strtotime("+{$durationMonths} months"));

            $stmt = $this->pdo->prepare(
                "INSERT INTO user_subscriptions (user_id, subscription_type_id, start_date, end_date, duration_months, status) 
                 VALUES (?, ?, ?, ?, ?, 'active')"
            );
            $stmt->execute([
                $dto->customerId,
                $dto->subscriptionTypeId,
                $startDate,
                $endDate,
                $durationMonths
            ]);

            $subscriptionId = $this->pdo->lastInsertId();

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
