<?php
    class SearchAvailableParkingsUseCase {
        private IParkingRepository $parkingRepository;

        public function __construct(IParkingRepository $parkingRepository) {
            $this->parkingRepository = $parkingRepository;
        }

        /**
         * @param float
         * @param float
         * @param float
         * @param DateTime
         * @return array
         */
        public function execute(float $latitude, float $longitude, float $radiusKm = 5.0, ?DateTime $checkTime = null): array {
            if ($checkTime === null) {
                $checkTime = new DateTime();
            }

            $parkings = $this->parkingRepository->findByLocation($latitude, $longitude, $radiusKm);
            $results = [];

            foreach ($parkings as $parking) {
                $parkingLocation = $parking->getLocation();
                
                if (!isset($parkingLocation['latitude']) || !isset($parkingLocation['longitude'])) {
                    continue;
                }

                $parkingLat = $parkingLocation['latitude'];
                $parkingLon = $parkingLocation['longitude'];

                $distance = $this->calculateDistance($latitude, $longitude, $parkingLat, $parkingLon);

                if ($distance <= $radiusKm) {
                    $availabilityUseCase = new GetParkingAvailabilityUseCase();
                    $availability = $availabilityUseCase->execute($parking, $checkTime);

                    if ($availability['availableSpaces'] > 0) {
                        $results[] = [
                            'parking' => $parking,
                            'distance' => round($distance, 2),
                            'availableSpaces' => $availability['availableSpaces'],
                            'capacity' => $parking->getCapacity(),
                            'location' => $parkingLocation,
                        ];
                    }
                }
            }

            usort($results, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });

            return [
                'parkings' => $results,
                'count' => count($results),
                'searchCenter' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ],
                'radiusKm' => $radiusKm,
                'checkTime' => $checkTime->format('Y-m-d H:i:s'),
            ];
        }

        /**
         * @param float $lat1 Latitude point 1
         * @param float $lon1 Longitude point 1
         * @param float $lat2 Latitude point 2
         * @param float $lon2 Longitude point 2
         * @return float Distance en kilomètres
         */
        private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
            $earthRadius = 6371; // Rayon de la Terre en km

            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            return $earthRadius * $c;
        }
    }
?>