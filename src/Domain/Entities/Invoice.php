<?php
    class Invoice {
        private string $id;
        private Reservation $reservation;
        private float $amount;
        private string $currency;
        private DateTime $generatedAt;

        public function __construct(Reservation $reservation, float $amount, string $currency = 'EUR') {
            if ($amount < 0) {
                throw new InvalidArgumentException('amount must be >= 0');
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
            // Utiliser l'autoloader de Composer pour FPDF
            if (!class_exists('Fpdf\\Fpdf')) {
                require_once __DIR__ . '/../../../vendor/autoload.php';
            }
            
            $pdf = new \Fpdf\Fpdf();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            
            // Titre
            $pdf->Cell(0, 10, 'Facture #' . $this->id, 0, 1, 'C');
            $pdf->Ln(10);
            
            // Informations
            $pdf->SetFont('Arial', '', 12);
            $r = $this->reservation;
            
            $pdf->Cell(0, 8, 'Client: ' . $r->getCustomer()->getId(), 0, 1);
            $pdf->Cell(0, 8, 'Parking: ' . $r->getParking()->getId(), 0, 1);
            $pdf->Cell(0, 8, 'Du: ' . $r->getStartTime()->format('Y-m-d H:i'), 0, 1);
            $pdf->Cell(0, 8, 'Au: ' . $r->getEndTime()->format('Y-m-d H:i'), 0, 1);
            $pdf->Ln(5);
            
            // Montant
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Montant: ' . number_format($this->amount, 2, ',', ' ') . ' ' . $this->currency, 0, 1);
            $pdf->Ln(5);
            
            // Date de génération
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 8, 'Généré le: ' . $this->generatedAt->format('Y-m-d H:i'), 0, 1);
            
            return $pdf->Output('S');
        }
    }
?>