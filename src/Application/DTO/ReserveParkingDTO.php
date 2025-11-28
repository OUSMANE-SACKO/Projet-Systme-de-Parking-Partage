<?php

class ReserveParkingDTO {
    public string $customerId;
    public string $parkingId;
    public string $from;
    public string $to;
    public ?string $vehiclePlate;

    public function __construct(string $customerId, string $parkingId, string $from, string $to, ?string $vehiclePlate = null) {
        $this->customerId = trim($customerId);
        $this->parkingId = trim($parkingId);
        $this->from = $from;
        $this->to = $to;
        $this->vehiclePlate = $vehiclePlate ? trim($vehiclePlate) : null;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['customerId'] ?? '',
            $data['parkingId'] ?? '',
            $data['from'] ?? '',
            $data['to'] ?? '',
            $data['vehiclePlate'] ?? null
        );
    }

    public function validate(): void {
        if ($this->customerId === '' || $this->parkingId === '') {
            throw new InvalidArgumentException('Client et parking requis.');
        }
        if ($this->from === '' || $this->to === '') {
            throw new InvalidArgumentException('Dates de réservation requises.');
        }
    }
}
