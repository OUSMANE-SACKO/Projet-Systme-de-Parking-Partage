<?php

class GetParkingsDTO {
    public ?string $city;
    public ?float $latitude;
    public ?float $longitude;
    public ?float $radius;

    public function __construct(?string $city = null, ?float $latitude = null, ?float $longitude = null, ?float $radius = null) {
        $this->city = $city ? trim($city) : null;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radius = $radius;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['city'] ?? null,
            isset($data['latitude']) ? (float)$data['latitude'] : null,
            isset($data['longitude']) ? (float)$data['longitude'] : null,
            isset($data['radius']) ? (float)$data['radius'] : null
        );
    }

    public function validate(): void {
        // Pas de validation requise, tous les champs sont optionnels
    }
}
