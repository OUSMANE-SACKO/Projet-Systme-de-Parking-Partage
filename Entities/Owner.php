<?php
    class Owner extends User {
        /** @var Parking[] */
        private array $parkings = [];

        //getters
        public function getParkings() : array {
            return $this->parkings;
        }

        //setters
        public function addParking(Parking $parking) : void {
            $this->parkings[] = $parking;
        }

        public function removeParking(Parking $parking) : bool {
            $index = array_search($parking, $this->parkings, true);
            if ($index === false) {
                return false;
            }
            array_splice($this->parkings, $index, 1);
            return true;
        }

        public function setParkings(array $parkings): void {
            foreach ($parkings as $p) {
                if (!$p instanceof Parking) {
                    throw new InvalidArgumentException('Tous les éléments doivent être des instances de Parking');
                }
            }
            $this->parkings = array_values($parkings);
        }
    }
?>