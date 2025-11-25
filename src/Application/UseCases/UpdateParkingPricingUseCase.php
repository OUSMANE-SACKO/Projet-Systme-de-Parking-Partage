<?php
    class UpdateParkingPricingUseCase {
        /**
         * 
         * @param Parking
         * @param array
         * @throws InvalidArgumentException
         */
        public function execute(Parking $parking, array $pricingSchedules): void {
            foreach ($pricingSchedules as $schedule) {
                if (!$schedule instanceof PricingSchedule) {
                    throw new InvalidArgumentException('All elements must be instances of PricingSchedule');
                }
            }
            
            $parking->setPricingSchedules($pricingSchedules);
        }
        
        public function addPricingSchedule(Parking $parking, DateTime $time, float $price): PricingSchedule {
            $schedule = new PricingSchedule($time, $price);
            $parking->addPricingSchedule($schedule);
            return $schedule;
        }
        
        public function removePricingSchedule(Parking $parking, PricingSchedule $schedule): bool {
            return $parking->removePricingSchedule($schedule);
        }
        
        public function clearAllPricingSchedules(Parking $parking): void {
            $parking->setPricingSchedules([]);
        }
    }
?>