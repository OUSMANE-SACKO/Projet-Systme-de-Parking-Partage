<?php
    class Customer extends User {
        /** @var Reservation[] */
        private array $reservations = [];

        /** @var ParkingSpace[] */
        private array $parkingSpaces = [];

        /** @var Subscription[] */
        private array $subscriptions = [];
        
        //getters
        public function getReservations() : array {
            return $this->reservations;
        }
        public function getParkingSpaces() : array {
            return $this->parkingSpaces;
        }
        public function getSubscriptions() : array {
            return $this->subscriptions;
        }

        //setters
        public function setReservations(array $reservations) : void {
            foreach ($reservations as $r) {
                if (!$r instanceof Reservation) {
                    throw new InvalidArgumentException('Tous les éléments doivent être des instances de Reservation');
                }
            }
            $this->reservations = array_values($reservations);
        }

        public function setParkingSpaces(array $parkingSpaces) : void {
            foreach ($parkingSpaces as $p) {
                if (!$p instanceof ParkingSpace) {
                    throw new InvalidArgumentException('Tous les éléments doivent être des instances de ParkingSpace');
                }
            }
            $this->parkingSpaces = array_values($parkingSpaces);
        }

        public function setSubscriptions(array $subscriptions) : void {
            foreach ($subscriptions as $s) {
                if (!$s instanceof Subscription) {
                    throw new InvalidArgumentException('Tous les éléments doivent être des instances de Subscription');
                }
            }
            $this->subscriptions = array_values($subscriptions);
        }

        // helpers
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

        public function addSubscription(Subscription $subscription) : void {
            $this->subscriptions[] = $subscription;
        }

        public function removeSubscription(Subscription $subscription) : bool {
            $index = array_search($subscription, $this->subscriptions, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->subscriptions, $index, 1);
            return true;
        }
    }
?>