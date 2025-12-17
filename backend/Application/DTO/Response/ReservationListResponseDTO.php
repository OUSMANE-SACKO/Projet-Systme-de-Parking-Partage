<?php
/**
 * DTO de réponse pour la liste des réservations
 */
class ReservationListResponseDTO {
    public array $reservations;
    public int $total;
    public int $activeCount;
    public int $pastCount;

    public function __construct(array $reservations, int $total, int $activeCount, int $pastCount) {
        $this->reservations = $reservations;
        $this->total = $total;
        $this->activeCount = $activeCount;
        $this->pastCount = $pastCount;
    }

    /**
     * @param Reservation[] $reservations
     */
    public static function fromReservations(array $reservations): self {
        $now = new DateTime();
        $formatted = [];
        $activeCount = 0;
        $pastCount = 0;

        foreach ($reservations as $reservation) {
            $isActive = $reservation->getEndTime() > $now;
            $status = $isActive ? 'active' : 'completed';

            if ($isActive) {
                $activeCount++;
            } else {
                $pastCount++;
            }

            $formatted[] = ReservationResponseDTO::fromReservation($reservation, $status)->toArray();
        }

        return new self($formatted, count($reservations), $activeCount, $pastCount);
    }

    public function toArray(): array {
        return [
            'reservations' => $this->reservations,
            'total' => $this->total,
            'activeCount' => $this->activeCount,
            'pastCount' => $this->pastCount,
        ];
    }
}
