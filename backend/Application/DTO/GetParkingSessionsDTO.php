<?php

class GetParkingSessionsDTO {
    public string $parkingId;
    public ?string $activeOnly; // 'true' pour les sessions actives uniquement

    public function __construct(string $parkingId, ?string $activeOnly = null) {
        $this->parkingId = trim($parkingId);
        $this->activeOnly = $activeOnly;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['activeOnly'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
    }
}
