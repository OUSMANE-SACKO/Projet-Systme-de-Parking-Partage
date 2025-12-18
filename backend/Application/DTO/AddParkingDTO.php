<?php

class AddParkingDTO {
    public string $ownerId;
    public string $address;
    public int $capacity;
    public float $pricePerHour;
    public ?string $description;

    public function __construct(string $ownerId, string $address, int $capacity, float $pricePerHour, ?string $description = null) {
        $this->ownerId = trim($ownerId);
        $this->address = trim($address);
        $this->capacity = $capacity;
        $this->pricePerHour = $pricePerHour;
        $this->description = $description ? trim($description) : null;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['ownerId'] ?? '',
            $data['address'] ?? '',
            (int)($data['capacity'] ?? 0),
            (float)($data['pricePerHour'] ?? 0),
            $data['description'] ?? null
        );
    }

    public function validate(): void {
        if ($this->ownerId === '') {
            throw new InvalidArgumentException('ownerId requis.');
        }
        if ($this->address === '') {
            throw new InvalidArgumentException('Adresse requise.');
        }
        if ($this->capacity <= 0) {
            throw new InvalidArgumentException('Capacité invalide.');
        }
        if ($this->pricePerHour < 0) {
            throw new InvalidArgumentException('Prix horaire invalide.');
        }
    }
}
