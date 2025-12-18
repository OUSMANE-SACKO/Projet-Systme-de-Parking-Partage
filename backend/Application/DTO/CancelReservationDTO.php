<?php
class CancelReservationDTO {
    public ?int $reservationId;
    public ?int $userId;

    public function __construct(?int $reservationId = null, ?int $userId = null) {
        $this->reservationId = $reservationId;
        $this->userId = $userId;
    }

    public static function fromArray(array $data): self {
        return new self(
            isset($data['reservationId']) ? (int)$data['reservationId'] : null,
            isset($data['userId']) ? (int)$data['userId'] : null
        );
    }

    public function validate(): void {
        if (!is_int($this->reservationId) || $this->reservationId <= 0) {
            throw new InvalidArgumentException('reservationId requis.');
        }
        if (!is_int($this->userId) || $this->userId <= 0) {
            throw new InvalidArgumentException('userId requis.');
        }
    }
}
