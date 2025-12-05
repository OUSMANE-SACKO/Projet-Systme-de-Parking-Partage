<?php
    class GetActiveParkingSuscriptionUseCase {
        public function execute(Parking $parking): array {
            // Récupère tous les abonnements du parking
            return $parking->getSubscriptions();
        }
        
        public function getActiveSubscriptions(Parking $parking, ?DateTime $dateTime = null): array {
            $subscriptions = $parking->getSubscriptions();
            $activeSubscriptions = [];
            $now = $dateTime ?? new DateTime();
            
            foreach ($subscriptions as $subscription) {
                $startDate = $subscription->getStartDate();
                $endDate = $subscription->getEndDate();
                
                if ($startDate <= $now && $endDate >= $now) {
                    $activeSubscriptions[] = $subscription;
                }
            }
            
            return $activeSubscriptions;
        }
}
?>