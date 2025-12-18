<?php

class GetParkingRevenueDTO {
    public string $parkingId;
    public int $month;
    public int $year;

    public function __construct(string $parkingId, int $month, int $year) {
        $this->parkingId = trim($parkingId);
        $this->month = $month;
        $this->year = $year;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['parkingId'] ?? '',
            (int)($data['month'] ?? date('m')),
            (int)($data['year'] ?? date('Y'))
        );
    }

    public function validate(): void {
        if ($this->parkingId === '') {
            throw new InvalidArgumentException('Parking ID requis.');
        }
        if ($this->month < 1 || $this->month > 12) {
            throw new InvalidArgumentException('Mois invalide.');
        }
        if ($this->year < 2020 || $this->year > 2100) {
            throw new InvalidArgumentException('Année invalide.');
        }
    }
}
