<?php
    class ParkingSpace {
        private string $id;
        private DateTime $entry;
        private DateTime $exit;

        public function __construct(string $id, DateTime $entry, DateTime $exit) {
            if ($exit < $entry) {
                throw new InvalidArgumentException('exit must be after or equal to entry');
            }
            $this->id = $id;
            $this->entry = $entry;
            $this->exit = $exit;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getEntry() : DateTime {
            return $this->entry;
        }

        public function getExit() : DateTime {
            return $this->exit;
        }

        //setters
        public function setEntry(DateTime $entry) : void {
            $this->entry = $entry;
        }

        public function setExit(DateTime $exit) : void {
            if ($exit < $this->entry) {
                throw new InvalidArgumentException('exit must be after or equal to entry');
            }
            $this->exit = $exit;
        }
    }
?>