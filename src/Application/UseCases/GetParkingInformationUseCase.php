<?php
    class GetParkingInformationUseCase {
        /**
         * Retourne toutes les informations d'un parking
         */
        public function execute(Parking $parking): array {
            return [
                'location' => $parking->getLocation(),
                'capacity' => $parking->getCapacity(),
                'availableSpaces' => $this->calculateAvailableSpaces($parking),
                'pricingTiers' => $this->formatPricingTiers($parking->getPricingTiers()),
                'subscriptionTypes' => $this->formatSubscriptionTypes($parking->getSubscriptions()),
            ];
        }
        
        private function calculateAvailableSpaces(Parking $parking): int {
            $occupiedSpaces = 0;
            
            foreach ($parking->getParkingSpaces() as $space) {
                if ($space->getEndTime() === null) {
                    $occupiedSpaces++;
                }
            }
            
            return $parking->getCapacity() - $occupiedSpaces;
        }
        
        private function formatPricingTiers(array $pricingTiers): array {
            $formatted = [];
            
            foreach ($pricingTiers as $tier) {
                $formatted[] = [
                    'time' => $schedule->getTime()->format('H:i'),
                    'price' => $schedule->getPrice()
                ];
            }
            
            return $formatted;
        }

        private function formatSubscriptionTypes(array $subscriptionTypes): array {
            $formatted = [];
            
            foreach ($subscriptionTypes as $type) {
                $formatted[] = [
                    'name' => $type->getName(),
                    'description' => $type->getDescription(),
                    'monthlyPrice' => $type->getMonthlyPrice(),
                    'durationMonths' => $type->getDurationMonths(),
                    'weeklyTimeSlots' => $type->getWeeklyTimeSlots(),
                    'isFullAccess' => empty($type->getWeeklyTimeSlots())
                ];
            }
            
            return $formatted;
        }
    }
?>