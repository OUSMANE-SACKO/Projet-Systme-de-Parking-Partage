<?php
/**
 * DTO de réponse pour la recherche de parkings
 */
class ParkingSearchResponseDTO {
    public array $parkings;
    public int $count;
    public array $searchCenter;
    public float $radiusKm;
    public string $checkTime;

    public function __construct(
        array $parkings,
        int $count,
        array $searchCenter,
        float $radiusKm,
        string $checkTime
    ) {
        $this->parkings = $parkings;
        $this->count = $count;
        $this->searchCenter = $searchCenter;
        $this->radiusKm = $radiusKm;
        $this->checkTime = $checkTime;
    }

    public static function fromSearchResults(array $results): self {
        $parkings = [];

        foreach ($results['parkings'] as $result) {
            $parking = $result['parking'];
            $location = $parking->getLocation();

            $parkings[] = [
                'id' => $parking->getId(),
                'name' => $location['name'] ?? 'Parking',
                'address' => $location['address'] ?? '',
                'city' => $location['city'] ?? '',
                'location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                ],
                'distance' => $result['distance'],
                'capacity' => $result['capacity'],
                'availableSpaces' => $result['availableSpaces'],
                'pricePerHour' => self::getMinPrice($parking),
            ];
        }

        return new self(
            $parkings,
            $results['count'],
            $results['searchCenter'],
            $results['radiusKm'],
            $results['checkTime']
        );
    }

    private static function getMinPrice(Parking $parking): float {
        $tiers = $parking->getPricingTiers();
        if (empty($tiers)) {
            return 2.0; // Prix par défaut
        }

        $minPrice = PHP_FLOAT_MAX;
        foreach ($tiers as $tier) {
            if ($tier->getPrice() < $minPrice) {
                $minPrice = $tier->getPrice();
            }
        }

        return $minPrice === PHP_FLOAT_MAX ? 2.0 : $minPrice;
    }

    public function toArray(): array {
        return [
            'parkings' => $this->parkings,
            'count' => $this->count,
            'searchCenter' => $this->searchCenter,
            'radiusKm' => $this->radiusKm,
            'checkTime' => $this->checkTime,
        ];
    }
}
