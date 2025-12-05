<?php

class EnterExitParkingDTO {
    public string $parkingId;
    public string $vehiclePlate;
    public ?string $timestamp;

    public function __construct(string $parkingId, string $vehiclePlate, ?string $timestamp = null) {
        $this->parkingId = trim($parkingId);
        $this->vehiclePlate = trim($vehiclePlate);
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['vehiclePlate'] ?? '',
            $data['timestamp'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '' || $this->vehiclePlate === '') {
            throw new InvalidArgumentException('Parking et plaque requis.');
        }
    }
}
