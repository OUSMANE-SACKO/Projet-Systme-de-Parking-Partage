<?php
/**
 * DTO de réponse pour une facture
 */
class InvoiceResponseDTO {
    public ?int $id;
    public int $reservationId;
    public float $amount;
    public string $currency;
    public string $generatedAt;
    public string $status;
    public array $reservation;
    public ?string $pdfUrl;

    public function __construct(
        ?int $id,
        int $reservationId,
        float $amount,
        string $currency,
        string $generatedAt,
        string $status,
        array $reservation,
        ?string $pdfUrl = null
    ) {
        $this->id = $id;
        $this->reservationId = $reservationId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->generatedAt = $generatedAt;
        $this->status = $status;
        $this->reservation = $reservation;
        $this->pdfUrl = $pdfUrl;
    }

    public static function fromInvoice(Invoice $invoice): self {
        $reservation = $invoice->getReservation();

        return new self(
            $invoice->getId(),
            $reservation->getId(),
            $invoice->getAmount(),
            $invoice->getCurrency(),
            $invoice->getGeneratedAt()->format('Y-m-d H:i:s'),
            'paid',
            [
                'id' => $reservation->getId(),
                'startTime' => $reservation->getStartTime()->format('Y-m-d H:i:s'),
                'endTime' => $reservation->getEndTime()->format('Y-m-d H:i:s'),
                'durationMinutes' => $reservation->getDurationMinutes(),
            ],
            null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'reservationId' => $this->reservationId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'generatedAt' => $this->generatedAt,
            'status' => $this->status,
            'reservation' => $this->reservation,
            'pdfUrl' => $this->pdfUrl,
        ];
    }
}
