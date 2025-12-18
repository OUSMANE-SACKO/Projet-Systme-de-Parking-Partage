<?php

class EnterExitParkingDTO {
    public string $parkingId;
    public string $action; // 'enter' ou 'exit'
    public ?string $timestamp;
    public ?int $userId;

    public function __construct(string $parkingId, string $action = 'enter', ?string $timestamp = null, ?int $userId = null) {
        $this->parkingId = trim($parkingId);
        $this->action = trim($action);
        $this->timestamp = $timestamp;
        $this->userId = $userId;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['action'] ?? 'enter',
            $data['timestamp'] ?? null,
            isset($data['userId']) ? (int)$data['userId'] : null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking requis.');
        }
        if (!in_array($this->action, ['enter', 'exit'])) {
            throw new InvalidArgumentException('Action invalide. Utilisez "enter" ou "exit".');
        }
        if (!is_int($this->userId) || $this->userId <= 0) {
            throw new InvalidArgumentException('Utilisateur non authentifié.');
        }
    }
}
