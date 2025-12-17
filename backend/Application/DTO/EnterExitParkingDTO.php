<?php

class EnterExitParkingDTO {
    public string $parkingId;
    public string $vehiclePlate;
    public string $action; // 'enter' ou 'exit'
    public ?string $timestamp;

    public function __construct(string $parkingId, string $vehiclePlate, string $action = 'enter', ?string $timestamp = null) {
        $this->parkingId = trim($parkingId);
        $this->vehiclePlate = trim($vehiclePlate);
        $this->action = trim($action);
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['vehiclePlate'] ?? '',
            $data['action'] ?? 'enter',
            $data['timestamp'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '' || $this->vehiclePlate === '') {
            throw new InvalidArgumentException('Parking et plaque requis.');
        }
        if (!in_array($this->action, ['enter', 'exit'])) {
            throw new InvalidArgumentException('Action invalide. Utilisez "enter" ou "exit".');
        }
    }
}
