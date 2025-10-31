<?php
    class Parking {
        private string $id;
        private array $location = [];
        private int $capacity;

        public function __construct(string $id, array $location, int $capacity) {
            if ($capacity < 0) {
                throw new InvalidArgumentException('capacity must be >= 0');
            }
            $this->id = $id;
            $this->location = $location;
            $this->capacity = $capacity;
        }

        //getters
        public function getLocation() : array {
            return $this->location;
        }

        public function getCapacity() : int {
            return $this->capacity;
        }

        public function getId() : string {
            return $this->id;
        }

        //setters
        public function setLocation(array $location) : void {
            $this->location = $location;
        }

        public function setCapacity(int $capacity) : void {
            $this->capacity = $capacity;
        }

        public function setId(string $id) : void {
            $this->id = $id;
        }
    }
?>