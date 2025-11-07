<?php
    class AddParkingUseCase {
        public function execute(Owner $owner, array $location, int $capacity, array $pricingSchedules = []) : Parking {
            $parking = new Parking($location, $capacity);
            
            // Ajouter les horaires tarifaires au parking
            foreach ($pricingSchedules as $schedule) {
                if ($schedule instanceof PricingSchedule) {
                    $parking->addPricingSchedule($schedule);
                } else {
                    throw new InvalidArgumentException('All items in pricingSchedules must be instances of PricingSchedule');
                }
            }
            
            $owner->addParking($parking);
            return $parking;
        }
    }
?>