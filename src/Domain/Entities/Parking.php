<?php
    class Parking {
        private ?int $id = null;
        // private array $location = [];
        private float $latitude;
        private float $longitude;
        private int $capacity;
        
        /** @var PricingTier[] */
        private array $pricingTiers = [];
        
        /** @var Reservation[] */
        private array $reservations = [];
        
        /** @var ParkingSpace[] */
        private array $parkingSpaces = [];
        
        /** @var SubscriptionType[] */
        private array $subscriptionsTypes = [];

        private array $openingHours = [];

        public function __construct(array $location, int $capacity) {
            if ($capacity < 0) {
                throw new InvalidArgumentException('capacity must be >= 0');
            }
            // $this->location = $location;
            $this->latitude = $location['latitude'];
            $this->longitude = $location['longitude'];
            $this->capacity = $capacity;

            $this->subscriptionsTypes = [];
            $this->pricingTiers = [];
            $this->reservations = [];
            $this->parkingSpaces = [];
        }
        
        //getters
        public function getId() : ?int {
            return $this->id;
        }

        public function getLocation() : array {
            return [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ];
        }

        public function getCapacity() : int {
            return $this->capacity;
        }

        public function getPricingTiers() : array {
            return $this->pricingTiers;
        }

        public function getReservations() : array {
            return $this->reservations;
        }

        public function getParkingSpaces() : array {
            return $this->parkingSpaces;
        }

        public function getSubscriptions() : array {
            return $this->subscriptionsTypes;
        }

        //setters
        public function setId(int $id) : void {
            $this->id = $id;
        }

        public function setLocation(array $location) : void {
            $this->latitude = $location['latitude'];
            $this->longitude = $location['longitude'];
        }

        public function setCapacity(int $capacity) : void {
            if ($capacity < 0) {
                throw new InvalidArgumentException('capacity must be >= 0');
            }
            $this->capacity = $capacity;
        }

        //helpers
        public function addPricingTier(PricingTier $tier) : void {
            $this->pricingTiers[] = $tier;
        }

        public function removePricingTier(PricingTier $tier) : bool {
            $index = array_search($tier, $this->pricingTiers, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->pricingTiers, $index, 1);
            return true;
        }

        public function setPricingTiers(array $tiers) : void {
            foreach ($tiers as $tier) {
                if (!$tier instanceof PricingTier) {
                    throw new InvalidArgumentException('All elements must be instances of PricingTier');
                }
            }
            $this->pricingTiers = array_values($tiers);
        }

        public function addReservation(Reservation $reservation) : void {
            $this->reservations[] = $reservation;
        }

        public function removeReservation(Reservation $reservation) : bool {
            $index = array_search($reservation, $this->reservations, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->reservations, $index, 1);
            return true;
        }

        public function addParkingSpace(ParkingSpace $parkingSpace) : void {
            $this->parkingSpaces[] = $parkingSpace;
        }

        public function removeParkingSpace(ParkingSpace $parkingSpace) : bool {
            $index = array_search($parkingSpace, $this->parkingSpaces, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->parkingSpaces, $index, 1);
            return true;
        }

        public function addSubscriptionType(SubscriptionType $subscriptionType) : void {
            $this->subscriptionsTypes[] = $subscriptionType;
        }

        public function removeSubscriptionType(SubscriptionType $subscriptionType) : bool {
            $index = array_search($subscriptionType, $this->subscriptionsTypes, true);;
            if ($index === false) {
                return false;
            }
            array_splice($this->subscriptionsTypes, $index, 1);
            return true;
        }

        public function getOccupiedSpacesCount(): int {
            $occupiedSpaces = 0;
            
            foreach ($this->parkingSpaces as $space) {
                if ($space->getEndTime() === null) {
                    $occupiedSpaces++;
                }
            }
            
            return $occupiedSpaces;
        }
    }
?>