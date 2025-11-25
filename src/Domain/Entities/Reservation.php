<?php
    class Reservation {
        private string $id;
        private Customer $customer;
        private Parking $parking;
        private DateTime $startTime;
        private DateTime $endTime;

        public function __construct(Customer $customer, Parking $parking, DateTime $startTime, DateTime $endTime) {
            if ($endTime < $startTime) {
                throw new InvalidArgumentException('endTime must be after startTime');
            }
            $this->id = uniqid('', true);
            $this->customer = $customer;
            $this->parking = $parking;
            $this->startTime = $startTime;
            $this->endTime = $endTime;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getCustomer() : Customer {
            return $this->customer;
        }

        public function getParking() : Parking {
            return $this->parking;
        }

        public function getStartTime() : DateTime {
            return $this->startTime;
        }

        public function getEndTime() : DateTime {
            return $this->endTime;
        }

        // computed helpers
        public function getDurationMinutes() : int {
            $seconds = max(0, $this->endTime->getTimestamp() - $this->startTime->getTimestamp());
            return (int) ceil($seconds / 60);
        }

        //setters
        public function setCustomer(Customer $customer) : void {
            $this->customer = $customer;
        }

        public function setParking(Parking $parking) : void {
            $this->parking = $parking;
        }

        public function setStartTime(DateTime $startTime) : void {
            if ($this->endTime < $startTime) {
                throw new InvalidArgumentException('startTime must be before endTime');
            }
            $this->startTime = $startTime;
        }

        public function setEndTime(DateTime $endTime) : void {
            if ($endTime < $this->startTime) {
                throw new InvalidArgumentException('endTime must be after startTime');
            }
            $this->endTime = $endTime;
        }
    }
?>