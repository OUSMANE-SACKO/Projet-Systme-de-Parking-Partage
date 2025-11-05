<?php
    class Reservation {
        private string $id;
        private DateTime $startTime;
        private DateTime $endTime;
        private float $price;
        private Parking $parking;

        public function __construct(string $id, DateTime $startTime, DateTime $endTime, float $price, Parking $parking) {
            if ($endTime < $startTime) {
                throw new InvalidArgumentException('endTime must be after startTime');
            }
            if ($price < 0) {
                throw new InvalidArgumentException('price must be >= 0');
            }
            $this->id = $id;
            $this->startTime = $startTime;
            $this->endTime = $endTime;
            $this->price = $price;
            $this->parking = $parking;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getStartTime() : DateTime {
            return $this->startTime;
        }

        public function getEndTime() : DateTime {
            return $this->endTime;
        }

        public function getPrice() : float {
            return $this->price;
        }

        public function getParking() : Parking {
            return $this->parking;
        }

        //setters
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

        public function setPrice(float $price) : void {
            if ($price < 0) {
                throw new InvalidArgumentException('price must be >= 0');
            }
            $this->price = $price;
        }

        public function setParking(Parking $parking) : void {
            $this->parking = $parking;
        }
    }
?>