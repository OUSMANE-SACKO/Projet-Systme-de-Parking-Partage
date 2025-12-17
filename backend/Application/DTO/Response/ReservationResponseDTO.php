<?php
/**
 * DTO de réponse pour une réservation
 */
class ReservationResponseDTO {
    public ?int $id;
    public int $customerId;
    public int $parkingId;
    public string $parkingName;
    public string $startTime;
    public string $endTime;
    public int $durationMinutes;
    public float $amount;
    public string $status;
    public ?string $vehiclePlate;

    public function __construct(
        ?int $id,
        int $customerId,
        int $parkingId,
        string $parkingName,
        string $startTime,
        string $endTime,
        int $durationMinutes,
        float $amount,
        string $status,
        ?string $vehiclePlate = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->parkingId = $parkingId;
        $this->parkingName = $parkingName;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->durationMinutes = $durationMinutes;
        $this->amount = $amount;
        $this->status = $status;
        $this->vehiclePlate = $vehiclePlate;
    }

    public static function fromReservation(Reservation $reservation, string $status = 'confirmed'): self {
        $parking = $reservation->getParking();
        $location = $parking->getLocation();

        return new self(
            $reservation->getId(),
            $reservation->getCustomer()->getId(),
            $parking->getId(),
            $location['name'] ?? 'Parking #' . $parking->getId(),
            $reservation->getStartTime()->format('Y-m-d H:i:s'),
            $reservation->getEndTime()->format('Y-m-d H:i:s'),
            $reservation->getDurationMinutes(),
            $reservation->getAmount(),
            $status,
            null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'customerId' => $this->customerId,
            'parkingId' => $this->parkingId,
            'parkingName' => $this->parkingName,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'durationMinutes' => $this->durationMinutes,
            'amount' => $this->amount,
            'status' => $this->status,
            'vehiclePlate' => $this->vehiclePlate,
        ];
    }
}
