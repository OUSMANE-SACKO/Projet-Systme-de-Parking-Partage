<?php
    class AddParkingUseCase {
        public function execute(Owner $owner, array $location, int $capacity, array $PricingTiers = []) : Parking {
            $parking = new Parking($location, $capacity);
            
            // Ajouter les horaires tarifaires au parking
            foreach ($PricingTiers as $tier) {
                if ($tier instanceof PricingTier) {
                    $parking->addPricingTier($tier);
                } else {
                    throw new InvalidArgumentException('All items in PricingTiers must be instances of PricingTier');
                }
            }
            
            $owner->addParking($parking);
            return $parking;
        }
    }
?>