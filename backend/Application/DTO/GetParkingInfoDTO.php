<?php

class GetParkingInfoDTO {
    public string $parkingId;

    public function __construct(string $parkingId) {
        $this->parkingId = trim($parkingId);
    }

    public static function fromArray(array $data): self {
        return new self($data['parkingId'] ?? '');
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
    }
}
