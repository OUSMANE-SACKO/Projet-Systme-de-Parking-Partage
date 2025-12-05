<?php
    class GetUnauthorizedDriversUseCase {
        /**
         * @param Parking
         * @param DateTime
         * @return array
         */

        public function execute(Parking $parking, DateTime $checkTime): array {
            $unauthorized = [];

            foreach ($parking->getParkingSpaces() as $space) {
                $customer = $space->getCustomer();
                
                if ($customer !== null && ($space->getEndTime() === null || $space->getEndTime() >= $checkTime)) {
                    $startTime = $space->getStartTime();
                    
                    $hasValidReservation = $this->hasValidReservation($customer, $parking, $checkTime);
                    $hasValidSubscription = $this->hasValidSubscription($customer, $parking, $checkTime);
                    
                    if (!$hasValidReservation && !$hasValidSubscription) {
                        $unauthorized[] = [
                            'customer' => $customer,
                            'parkingSpace' => $space,
                            'parkedSince' => $startTime->format('Y-m-d H:i:s'),
                            'checkTime' => $checkTime->format('Y-m-d H:i:s'),
                        ];
                    }
                }
            }

            return [
                'unauthorizedDrivers' => $unauthorized,
                'count' => count($unauthorized),
                'checkTime' => $checkTime->format('Y-m-d H:i:s'),
            ];
        }

        private function hasValidReservation(Customer $customer, Parking $parking, DateTime $checkTime): bool {
            foreach ($parking->getReservations() as $reservation) {
                if ($reservation->getCustomer() === $customer) {
                    $start = $reservation->getStartTime();
                    $end = $reservation->getEndTime();
                    
                    // Vérifier si checkTime est dans le créneau
                    if ($checkTime >= $start && $checkTime <= $end) {
                        return true;
                    }
                }
            }
            return false;
        }

        private function hasValidSubscription(Customer $customer, Parking $parking, DateTime $checkTime): bool {
            foreach ($customer->getSubscriptions() as $subscription) {
                $start = $subscription->getStartDate();
                $end = $subscription->getEndDate();
                
                if ($checkTime >= $start && $checkTime <= $end) {
                    $subscriptionType = $subscription->getSubscriptionType();
                    $timeSlots = $subscriptionType->getWeeklyTimeSlots();
                    
                    if (empty($timeSlots)) {
                        return true;
                    }
                    
                    $dayOfWeek = $checkTime->format('l');
                    $currentTime = $checkTime->format('H:i');
                    
                    foreach ($timeSlots as $slot) {
                        if ($slot['day'] === $dayOfWeek) {
                            if ($currentTime >= $slot['startTime'] && $currentTime <= $slot['endTime']) {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        }
    }
?>