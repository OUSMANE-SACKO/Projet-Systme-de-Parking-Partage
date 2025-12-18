<?php

class UpdateParkingPricingDTO {
    public string $parkingId;
    public float $hourlyRate;
    public ?array $pricingTiers; // [['time' => '08:00', 'price' => 3.50], ...]

    public function __construct(string $parkingId, float $hourlyRate, ?array $pricingTiers = null) {
        $this->parkingId = trim($parkingId);
        $this->hourlyRate = $hourlyRate;
        $this->pricingTiers = $pricingTiers;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            (float)($data['hourlyRate'] ?? 0),
            $data['pricingTiers'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
        if ($this->hourlyRate < 0) {
            throw new InvalidArgumentException('Tarif horaire invalide.');
        }
    }
}
