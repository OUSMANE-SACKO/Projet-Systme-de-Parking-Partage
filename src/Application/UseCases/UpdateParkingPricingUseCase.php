<?php
    class UpdateParkingPricingUseCase {
        /**
         * 
         * @param Parking
         * @param array
         * @throws InvalidArgumentException
         */
        public function execute(Parking $parking, array $pricingTiers): void {
            foreach ($pricingTiers as $tier) {
                if (!$tier instanceof PricingSchedule) {
                    throw new InvalidArgumentException('All elements must be instances of PricingSchedule');
                }
            }
            
            $parking->setPricingSchedules($pricingTiers);
        }
        
        public function addPricingSchedule(Parking $parking, DateTime $time, float $price): PricingSchedule {
            $tier = new PricingSchedule($time, $price);
            $parking->addPricingSchedule($tier);
            return $tier;
        }
        
        public function removePricingSchedule(Parking $parking, PricingSchedule $tier): bool {
            return $parking->removePricingSchedule($tier);
        }
        
        public function clearAllPricingSchedules(Parking $parking): void {
            $parking->setPricingSchedules([]);
        }
    }
?>