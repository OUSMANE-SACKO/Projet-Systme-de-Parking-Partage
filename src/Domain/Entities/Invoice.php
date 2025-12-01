<?php
    class Invoice {
        private string $id;
        private Reservation $reservation;
        private float $amount;
        private string $currency;
        private DateTime $generatedAt;

        public function __construct(Reservation $reservation, float $amount, string $currency = 'EUR') {
            if ($amount < 0) {
                throw new InvalidArgumentException('amount must be > 0');
            }
            $this->id = uniqid('', true);
            $this->reservation = $reservation;
            $this->amount = $amount;
            $this->currency = $currency;
            $this->generatedAt = new DateTime();
        }

        public function getId(): string { return $this->id; }
        public function getReservation(): Reservation { return $this->reservation; }
        public function getAmount(): float { return $this->amount; }
        public function getCurrency(): string { return $this->currency; }
        public function getGeneratedAt(): DateTime { return $this->generatedAt; }

        public function toHtml(): string {
            $r = $this->reservation;
            return '<div class="invoice">'
                .'<h2>Facture #'.$this->id.'</h2>'
                .'<p>Client: '.htmlspecialchars($r->getCustomer()->getId()).'</p>'
                .'<p>Parking: '.htmlspecialchars($r->getParking()->getId()).'</p>'
                .'<p>Du: '.$r->getStartTime()->format('Y-m-d H:i').'</p>'
                .'<p>Au: '.$r->getEndTime()->format('Y-m-d H:i').'</p>'
                .'<p>Montant: '.number_format($this->amount, 2, ',', ' ').' '.$this->currency.'</p>'
                .'<p>Généré le: '.$this->generatedAt->format('Y-m-d H:i').'</p>'
                .'</div>';
        }

        public function toPdfBinary(): string {
            return '%PDF-FAKE-INVOICE-'.$this->id; 
        }
    }
?>