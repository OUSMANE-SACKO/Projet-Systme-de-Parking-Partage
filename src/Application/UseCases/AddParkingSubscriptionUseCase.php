<?php
    class AddParkingSubscriptionUseCase {
        public function execute(Parking $parking, string $name, string $description, float $monthlyPrice, array $weeklyTimeSlots): Subscription {
            $subscription = new SubscriptionType($name, $description, $monthlyPrice, $weeklyTimeSlots);

            $parking->addSubscriptionType($subscription);

            return $subscription;
        }
    }
?>