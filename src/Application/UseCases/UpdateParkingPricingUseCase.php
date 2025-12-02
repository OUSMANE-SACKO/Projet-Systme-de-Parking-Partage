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
                if (!$tier instanceof PricingTier) {
                    throw new InvalidArgumentException('All elements must be instances of PricingTier');
                }
            }
            
            $parking->setPricingTiers($pricingTiers);
        }
        
        public function addPricingTier(Parking $parking, DateTime $time, float $price): PricingTier {
            $tier = new PricingTier($time, $price);
            $parking->addPricingTier($tier);
            return $tier;
        }
        
        public function removePricingTier(Parking $parking, PricingTier $tier): bool {
            return $parking->removePricingTier($tier);
        }
        
        public function clearAllPricingTiers(Parking $parking): void {
            $parking->setPricingTiers([]);
        }
    }
?>