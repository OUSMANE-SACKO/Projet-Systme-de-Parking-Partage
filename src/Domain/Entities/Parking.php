<?php
    class Parking {
        private string $id;
        private array $location = [];
        private int $capacity;
        
        /** @var PricingSchedule[] */
        private array $pricingSchedules = [];
        
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
            $this->id = uniqid('', true);
            $this->location = $location;
            $this->capacity = $capacity;

            $this->subscriptionsTypes = [];
            $this->pricingSchedules = [];
            $this->reservations = [];
            $this->parkingSpaces = [];
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getLocation() : array {
            return $this->location;
        }

        public function getCapacity() : int {
            return $this->capacity;
        }

        public function getPricingSchedules() : array {
            return $this->pricingSchedules;
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
        public function setLocation(array $location) : void {
            if (empty($location) || !isset($location['longitude']) || !isset($location['latitude'])) {
                throw new InvalidArgumentException('location must contain longitude and latitude');
            }
            $this->location = $location;
        }

        public function setCapacity(int $capacity) : void {
            if ($capacity <= 0) {
                throw new InvalidArgumentException('capacity must be >= 0');
            }
            $this->capacity = $capacity;
        }

        //helpers
        public function addPricingSchedule(PricingSchedule $schedule) : void {
            $this->pricingSchedules[] = $schedule;
        }

        public function removePricingSchedule(PricingSchedule $schedule) : bool {
            $index = array_search($schedule, $this->pricingSchedules, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->pricingSchedules, $index, 1);
            return true;
        }

        public function setPricingSchedules(array $schedules) : void {
            foreach ($schedules as $s) {
                if (!$s instanceof PricingSchedule) {
                    throw new InvalidArgumentException('All elements must be instances of PricingSchedule');
                }
            }
            $this->pricingSchedules = array_values($schedules);
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