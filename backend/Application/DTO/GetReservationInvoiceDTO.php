<?php

class GetReservationInvoiceDTO {
    public string $reservationId;
    public string $format; // 'html' ou 'pdf'

    public function __construct(string $reservationId, string $format = 'html') {
        $this->reservationId = trim($reservationId);
        $this->format = trim($format);
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['reservationId'] ?? '',
            $data['format'] ?? 'html'
        );
    }

    public function validate(): void {
        if ($this->reservationId === '') {
            throw new InvalidArgumentException('Reservation ID requis.');
        }
        if (!in_array($this->format, ['html', 'pdf'])) {
            throw new InvalidArgumentException('Format invalide. Utilisez "html" ou "pdf".');
        }
    }
}
