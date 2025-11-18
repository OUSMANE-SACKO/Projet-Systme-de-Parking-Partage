<?php
    class PricingSchedule {
        private string $id;
        private DateTime $time;
        private float $price;

        public function __construct(DateTime $time, float $price) {
            if ($price < 0) {
                throw new InvalidArgumentException('price must be >= 0');
            }
            $this->id = uniqid('', true);
            $this->time = $time;
            $this->price = $price;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getTime() : DateTime {
            return $this->time;
        }

        public function getPrice() : float {
            return $this->price;
        }

        //setters
        public function setId(string $id) : void {
            $this->id = $id;
        }

        public function setTime(DateTime $time) : void {
            $this->time = $time;
        }

        public function setPrice(float $price) : void {
            $this->price = $price;
        }
    }
?>