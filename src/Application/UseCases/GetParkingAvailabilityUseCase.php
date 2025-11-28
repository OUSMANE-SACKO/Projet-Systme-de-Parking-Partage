<?php
    class GetParkingAvailabilityUseCase {
        /**
         * @param Parking
         * @param DateTime
         * @return array
         */

        public function execute(Parking $parking, DateTime $dateTime): array {
            $capacity = $parking->getCapacity();
            $occupiedSpaces = $this->countOccupiedSpacesAt($parking, $dateTime);
            $reservedSpaces = $this->countReservedSpacesAt($parking, $dateTime);
            
            $totalOccupied = $occupiedSpaces + $reservedSpaces;
            $availableSpaces = $capacity - $totalOccupied;
            
            return [
                'dateTime' => $dateTime->format('Y-m-d H:i:s'),
                'occupiedByVehicles' => $occupiedSpaces,
                'reservedSpaces' => $reservedSpaces,
                'totalOccupied' => $totalOccupied,
                'availableSpaces' => max(0, $availableSpaces),
            ];
        }
        
        private function countOccupiedSpacesAt(Parking $parking, DateTime $dateTime): int {
            $count = 0;
            
            foreach ($parking->getParkingSpaces() as $space) {
                if ($this->isSpaceOccupiedAt($space, $dateTime)) {
                    $count++;
                }
            }
            
            return $count;
        }
        
        private function countReservedSpacesAt(Parking $parking, DateTime $dateTime): int {
            $count = 0;
            
            foreach ($parking->getReservations() as $reservation) {
                if ($this->isReservationActiveAt($reservation, $dateTime)) {
                    $count++;
                }
            }
            
            return $count;
        }
        
        private function isSpaceOccupiedAt(ParkingSpace $space, DateTime $dateTime): bool {
            $startTime = $space->getStartTime();
            $endTime = $space->getEndTime();
            
            if ($endTime === null) {
                return $dateTime >= $startTime;
            }
            
            return ($dateTime >= $startTime && $dateTime <= $endTime);
        }
        
        private function isReservationActiveAt(Reservation $reservation, DateTime $dateTime): bool {
            return ($dateTime >= $reservation->getStartTime() && $dateTime <= $reservation->getEndTime());
        }
    }
?>