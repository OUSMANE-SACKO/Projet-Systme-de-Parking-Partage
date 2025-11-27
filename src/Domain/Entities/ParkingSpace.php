<?php
    class ParkingSpace {
        private string $id;
        private ?Customer $customer;
        private DateTime $startTime;
        private ?DateTime $endTime;
        private Parking $parking;
        private ?Reservation $reservation = null;
        private float $penaltyAmount = 0.0;

        public function __construct(?Customer $customer = null, DateTime $startTime, Parking $parking, ?DateTime $endTime = null) {
            $this->id = uniqid('', true);
            $this->customer = $customer;
            $this->startTime = $startTime;
            $this->endTime = $endTime;
            $this->parking = $parking;
        }

        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getCustomer() : ?Customer {
            return $this->customer;
        }

        public function getStartTime() : DateTime {
            return $this->startTime;
        }

        public function getEndTime() : ?DateTime {
            return $this->endTime;
        }

        public function getParking() : Parking {
            return $this->parking;
        }

        public function getReservation() : ?Reservation {
            return $this->reservation;
        }

        public function getPenaltyAmount() : float {
            return $this->penaltyAmount;
        }

        //setters
        public function setId(string $id) : void {
            $this->id = $id;
        }

        public function setCustomer(Customer $customer) : void {
            $this->customer = $customer;
        }

        public function setStartTime(DateTime $startTime) : void {
            $this->startTime = $startTime;
        }

        public function setEndTime(?DateTime $endTime) : void {
            $this->endTime = $endTime;
        }

        public function setParking(Parking $parking) : void {
            $this->parking = $parking;
        }

        public function setReservation(?Reservation $reservation) : void {
            $this->reservation = $reservation;
        }

        public function setPenaltyAmount(float $amount) : void {
            if ($amount < 0) {
                throw new InvalidArgumentException('penaltyAmount must be >= 0');
            }
            $this->penaltyAmount = $amount;
        }
    }
?>