<?php
class GetParkingSpacesUseCase {
    /**
     * 
     * @param Parking
     * @return array
     */
    public function execute(Parking $parking): array {
        $parkingSpaces = $parking->getParkingSpaces();
        
        return [
            'total' => count($parkingSpaces),
            'occupied' => $this->countOccupiedSpaces($parkingSpaces),
            'available' => $parking->getCapacity() - $this->countOccupiedSpaces($parkingSpaces),
            'capacity' => $parking->getCapacity(),
            'parkingSpaces' => $this->formatParkingSpaces($parkingSpaces)
        ];
    }
    
    private function countOccupiedSpaces(array $parkingSpaces): int {
        $count = 0;
        
        foreach ($parkingSpaces as $space) {
            if ($space->getEndTime() === null || $space->getEndTime() > new DateTime()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    private function formatParkingSpaces(array $parkingSpaces): array {
        $formatted = [];
        
        foreach ($parkingSpaces as $space) {
            $isOccupied = ($space->getEndTime() === null || $space->getEndTime() > new DateTime());
            
            // Calculer la durée selon le statut
            if (!$isOccupied) {
                // Espace libéré : durée entre début et fin
                $interval = $space->getStartTime()->diff($space->getEndTime());
                $duration = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            } else {
                // Espace occupé : durée depuis le début jusqu'à maintenant
                $interval = $space->getStartTime()->diff(new DateTime());
                $duration = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            }
            
            $formatted[] = [
                'id' => $space->getId(),
                'customer' => [
                    'id' => $space->getCustomer()->getId(),
                    'email' => $space->getCustomer()->getEmail()
                ],
                'startTime' => $space->getStartTime()->format('Y-m-d H:i:s'),
                'endTime' => $space->getEndTime() ? $space->getEndTime()->format('Y-m-d H:i:s') : null,
                'duration' => $duration,
                'status' => $isOccupied ? 'occupied' : 'completed'
            ];
        }
        
        usort($formatted, function($a, $b) {
            return strtotime($b['startTime']) <=> strtotime($a['startTime']);
        });
        
        return $formatted;
    }
    
    public function getOccupiedSpaces(Parking $parking): array {
        $allSpaces = $this->execute($parking);
        
        return array_filter($allSpaces['parkingSpaces'], function($space) {
            return $space['status'] === 'occupied';
        });
    }
}
?>