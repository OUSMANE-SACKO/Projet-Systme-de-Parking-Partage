<?php
    class Subscription {
        private string $id;
        private DateTime $startDate;
        private DateTime $endDate;
        private string $description;
        private float $price;

        private Parking $parking;

        public function __construct(string $id, DateTime $startDate, DateTime $endDate, string $description, float $price, Parking $parking) {
            if ($endDate < $startDate) {
                throw new InvalidArgumentException('endDate must be after startDate');
            }
            if ($price < 0) {
                throw new InvalidArgumentException('price must be >= 0');
            }
            $this->id = $id;
            $this->startDate = $startDate;
            $this->endDate = $endDate;
            $this->description = $description;
            $this->price = $price;
            $this->parking = $parking;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getStartDate() : DateTime {
            return $this->startDate;
        }

        public function getEndDate() : DateTime {
            return $this->endDate;
        }

        public function getDescription() : string {
            return $this->description;
        }

        public function getPrice() : float {
            return $this->price;
        }

        public function getParking() : Parking {
            return $this->parking;
        }

        //setters
        public function setStartDate(DateTime $startDate) : void {
            if ($this->endDate < $startDate) {
                throw new InvalidArgumentException('startDate must be before endDate');
            }
            $this->startDate = $startDate;
        }

        public function setEndDate(DateTime $endDate) : void {
            if ($endDate < $this->startDate) {
                throw new InvalidArgumentException('endDate must be after startDate');
            }
            $this->endDate = $endDate;
        }

        public function setDescription(string $description) : void {
            $this->description = $description;
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