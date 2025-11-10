<?php
    class GetParkingSubscriptionsUseCase {
        public function execute(Parking $parking): array {
            // Récupère tous les abonnements du parking
            return $parking->getSubscriptions();
        }
        
        public function getActiveSubscriptions(Parking $parking): array {
            $subscriptions = $parking->getSubscriptions();
            $activeSubscriptions = [];
            $now = new DateTime();
            
            foreach ($subscriptions as $subscription) {
                if ($subscription->getStartDate() <= $now && $subscription->getEndDate() >= $now) {
                    $activeSubscriptions[] = $subscription;
                }
            }
            
            return $activeSubscriptions;
        }
}
?>