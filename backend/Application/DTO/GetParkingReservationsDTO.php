<?php

class GetParkingReservationsDTO {
    public string $parkingId;
    public ?string $status; // 'active', 'upcoming', 'past', null pour tous

    public function __construct(string $parkingId, ?string $status = null) {
        $this->parkingId = trim($parkingId);
        $this->status = $status ? trim($status) : null;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            $data['status'] ?? null
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
    }
}
