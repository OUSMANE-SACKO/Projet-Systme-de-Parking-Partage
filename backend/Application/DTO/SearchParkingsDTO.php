<?php

class SearchParkingsDTO {
    public float $latitude;
    public float $longitude;
    public float $radiusKm;
    public ?string $timestamp;

    public function __construct(float $latitude, float $longitude, float $radiusKm = 5.0, ?string $timestamp = null) {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radiusKm = $radiusKm;
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self {
        return new self(
            (float)($data['latitude'] ?? 0),
            (float)($data['longitude'] ?? 0),
            (float)($data['radiusKm'] ?? 5.0),
            $data['timestamp'] ?? null
        );
    }

    public function validate(): void {
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw new InvalidArgumentException('Latitude invalide.');
        }
        if ($this->longitude < -180 || $this->longitude > 180) {
            throw new InvalidArgumentException('Longitude invalide.');
        }
        if ($this->radiusKm <= 0) {
            throw new InvalidArgumentException('Rayon invalide.');
        }
    }
}
