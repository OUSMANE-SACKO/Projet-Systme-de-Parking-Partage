<?php
    class AddParkingSubscriptionUseCase {
        public function execute(Parking $parking, string $name, string $description, float $monthlyPrice, array $weeklyTimeSlots): SubscriptionType {
            $subscription = new SubscriptionType($name, $description, $monthlyPrice, 12, $weeklyTimeSlots); // Default 12 months duration

            $parking->addSubscriptionType($subscription);

            return $subscription;
        }
    }
?>