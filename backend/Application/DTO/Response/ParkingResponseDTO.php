<?php
/**
 * DTO de réponse pour les informations d'un parking
 */
class ParkingResponseDTO {
    public ?int $id;
    public string $name;
    public string $address;
    public string $city;
    public array $location;
    public int $capacity;
    public int $availableSpaces;
    public array $pricingTiers;
    public array $subscriptionTypes;
    public array $openingHours;

    public function __construct(
        ?int $id,
        string $name,
        string $address,
        string $city,
        array $location,
        int $capacity,
        int $availableSpaces,
        array $pricingTiers = [],
        array $subscriptionTypes = [],
        array $openingHours = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->location = $location;
        $this->capacity = $capacity;
        $this->availableSpaces = $availableSpaces;
        $this->pricingTiers = $pricingTiers;
        $this->subscriptionTypes = $subscriptionTypes;
        $this->openingHours = $openingHours;
    }

    public static function fromParking(Parking $parking, int $availableSpaces): self {
        $pricingTiers = [];
        foreach ($parking->getPricingTiers() as $tier) {
            $pricingTiers[] = [
                'id' => $tier->getId(),
                'time' => $tier->getTime()->format('H:i'),
                'price' => $tier->getPrice(),
            ];
        }

        $subscriptionTypes = [];
        foreach ($parking->getSubscriptions() as $type) {
            $subscriptionTypes[] = [
                'id' => $type->getId(),
                'name' => $type->getName(),
                'description' => $type->getDescription(),
                'monthlyPrice' => $type->getMonthlyPrice(),
                'durationMonths' => $type->getDurationMonths(),
            ];
        }

        $location = $parking->getLocation();

        return new self(
            $parking->getId(),
            $location['name'] ?? 'Parking',
            $location['address'] ?? '',
            $location['city'] ?? '',
            [
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ],
            $parking->getCapacity(),
            $availableSpaces,
            $pricingTiers,
            $subscriptionTypes,
            $parking->getOpeningHours ?? []
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'availableSpaces' => $this->availableSpaces,
            'pricingTiers' => $this->pricingTiers,
            'subscriptionTypes' => $this->subscriptionTypes,
            'openingHours' => $this->openingHours,
        ];
    }
}
