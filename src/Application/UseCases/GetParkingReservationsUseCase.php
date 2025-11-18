<?php
    class GetParkingReservationsUseCase {
        /**
         * 
         * @param Parking
         * @return array
         */
        public function execute(Parking $parking): array {
            $reservations = $parking->getReservations();
            
            return [
                'total' => count($reservations),
                'reservations' => $this->formatReservations($reservations)
            ];
        }
        
        private function formatReservations(array $reservations): array {
            $formatted = [];
            $now = new DateTime();
            
            foreach ($reservations as $reservation) {
                $status = $this->getReservationStatus($reservation, $now);
                
                $formatted[] = [
                    'id' => $reservation->getId(),
                    'customer' => [
                        'id' => $reservation->getCustomer()->getId(),
                        'email' => $reservation->getCustomer()->getEmail()
                    ],
                    'startTime' => $reservation->getStartTime()->format('Y-m-d H:i:s'),
                    'endTime' => $reservation->getEndTime()->format('Y-m-d H:i:s'),
                    'duration' => $reservation->getDuration(),
                    'status' => $status
                ];
            }
            
            usort($formatted, function($a, $b) {
                return strtotime($b['startTime']) <=> strtotime($a['startTime']);
            });
            
            return $formatted;
        }
        
        private function getReservationStatus(Reservation $reservation, DateTime $now): string {
            if ($reservation->getStartTime() <= $now && $reservation->getEndTime() >= $now) {
                return 'active';
            } elseif ($reservation->getStartTime() > $now) {
                return 'upcoming';
            } else {
                return 'past';
            }
        }
    }
?>