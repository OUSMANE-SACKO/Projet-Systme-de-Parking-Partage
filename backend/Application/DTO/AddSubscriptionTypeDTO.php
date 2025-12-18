<?php

class AddSubscriptionTypeDTO {
    public string $parkingId;
    public string $name;
    public ?string $description;
    public float $monthlyPrice;
    public int $durationMonths;

    public function __construct(string $parkingId, string $name, ?string $description, float $monthlyPrice, int $durationMonths) {
        $this->parkingId = trim($parkingId);
        $this->name = trim($name);
        $this->description = $description ? trim($description) : null;
        $this->monthlyPrice = $monthlyPrice;
        $this->durationMonths = $durationMonths;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['name'] ?? '',
            $data['description'] ?? null,
            (float)($data['monthlyPrice'] ?? 0),
            (int)($data['durationMonths'] ?? 1)
        );
    }

    public function validate(): void {
        if ($this->parkingId === '' || $this->name === '') {
            throw new InvalidArgumentException('Parking ID et nom requis.');
        }
        if ($this->monthlyPrice <= 0) {
            throw new InvalidArgumentException('Prix mensuel invalide.');
        }
        if ($this->durationMonths < 1) {
            throw new InvalidArgumentException('Durée invalide.');
        }
    }
}
