<?php

class GetUnauthorizedDriversDTO {
    public string $parkingId;
    public ?string $timestamp;

    public function __construct(string $parkingId, ?string $timestamp = null) {
        $this->parkingId = trim($parkingId);
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['timestamp'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
    }
}
