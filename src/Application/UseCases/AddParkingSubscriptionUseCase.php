<?php
    class AddParkingSubscriptionUseCase {
        public function execute(Parking $parking, Customer $customer, DateTime $startDate, DateTime $endDate, array $weeklyTimeSlots = []): Subscription {
            $subscription = new SubscriptionType($customer, $startDate, $endDate, $weeklyTimeSlots);

            $parking->addSubscription($subscription);

            return $subscription;
        }
    }
?>