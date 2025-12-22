<?php

class EnterExitParkingDTO {
    public string $parkingId;
    public string $vehiclePlate;
    public ?string $timestamp;
    public ?int $userId;
    public string $action; // 'enter' ou 'exit'

    public function __construct(string $parkingId, string $vehiclePlate = '', ?string $timestamp = null, ?int $userId = null, string $action = 'enter') {
        $this->parkingId = trim($parkingId);
        $this->vehiclePlate = trim($vehiclePlate);
        $this->timestamp = $timestamp;
        $this->userId = $userId;
        $this->action = trim($action);
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['vehiclePlate'] ?? '',
            $data['timestamp'] ?? null,
            isset($data['userId']) ? (int)$data['userId'] : null,
            $data['action'] ?? 'enter'
        );
    }

    public function validate(): void {
        if ($this->parkingId === '' || $this->vehiclePlate === '') {
            throw new InvalidArgumentException('Parking et plaque requis.');
        }
        if (!in_array($this->action, ['enter', 'exit'])) {
            throw new InvalidArgumentException('Action invalide. Utilisez "enter" ou "exit".');
        }
        if (!is_int($this->userId) || $this->userId <= 0) {
            throw new InvalidArgumentException('Utilisateur non authentifié.');
        }
    }
}
