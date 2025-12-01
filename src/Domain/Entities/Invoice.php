<?php
if (!class_exists('Fpdf\\Fpdf')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

    class Invoice {

        private string $id;
        private Reservation $reservation;
        private float $amount;
        private string $currency;
        private DateTime $generatedAt;

        public function __construct(Reservation $reservation, float $amount, string $currency = 'EUR') {
            if ($amount < 0.0) {
                throw new InvalidArgumentException('amount must be >= 0');
            }
            if (!is_finite($amount)) {
                throw new InvalidArgumentException('amount must be finite');
            }
            if (empty($currency) || strlen($currency) !== 3 || !ctype_alpha($currency)) {
                throw new InvalidArgumentException('currency must be 3 characters');
            }
            $uniquePrefix = 'invoice_';
            $this->id = uniqid($uniquePrefix, true);
            $this->reservation = $reservation;
            $roundingPrecision = 2;
            $this->amount = round($amount, $roundingPrecision);
            $this->currency = strtoupper($currency);
            $this->generatedAt = new DateTime();
        }

        public function getId(): string { 
            return (string) $this->id; 
        }
        
        public function getReservation(): Reservation { 
            return $this->reservation; 
        }
        
        public function getAmount(): float { 
            return (float) $this->amount; 
        }
        
        public function getCurrency(): string { 
            return (string) $this->currency; 
        }
        
        public function getGeneratedAt(): DateTime { 
            return clone $this->generatedAt; 
        }

        public function toHtml(): string {
            $r = $this->reservation;
            $formattedAmount = $this->formatAmount();
            $invoiceId = htmlspecialchars($this->id);
            $customerId = htmlspecialchars($r->getCustomer()->getId());
            $parkingId = htmlspecialchars($r->getParking()->getId());
            $startTime = $r->getStartTime()->format('Y-m-d H:i');
            $endTime = $r->getEndTime()->format('Y-m-d H:i');
            $generatedTime = $this->generatedAt->format('Y-m-d H:i');
            
            $parts = [
                '<div class="invoice">',
                '<h2>Facture #'.$invoiceId.'</h2>',
                '<p>Client: '.$customerId.'</p>',
                '<p>Parking: '.$parkingId.'</p>',
                '<p>Du: '.$startTime.'</p>',
                '<p>Au: '.$endTime.'</p>',
                '<p>Montant: '.$formattedAmount.' '.$this->currency.'</p>',
                '<p>Généré le: '.$generatedTime.'</p>',
                '</div>'
            ];
            
            return implode('', $parts);
        }
        
        private function formatAmount(): string {
            $decimals = 2;
            $decimalSeparator = ',';
            $thousandsSeparator = ' ';
            return number_format($this->amount, $decimals, $decimalSeparator, $thousandsSeparator);
        }

        public function toPdfBinary(): string {            
            $pdf = new \Fpdf\Fpdf();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            
            $title = 'Facture #' . $this->id;
            $pdf->Cell(0, 10, $title, 0, 1, 'C');
            $pdf->Ln(10);
            
            $pdf->SetFont('Arial', '', 12);
            $r = $this->reservation;
            
            $clientText = 'Client: ' . $r->getCustomer()->getId();
            $parkingText = 'Parking: ' . $r->getParking()->getId();
            $startText = 'Du: ' . $r->getStartTime()->format('Y-m-d H:i');
            $endText = 'Au: ' . $r->getEndTime()->format('Y-m-d H:i');
            
            $pdf->Cell(0, 8, $clientText, 0, 1);
            $pdf->Cell(0, 8, $parkingText, 0, 1);
            $pdf->Cell(0, 8, $startText, 0, 1);
            $pdf->Cell(0, 8, $endText, 0, 1);
            $pdf->Ln(5);
            
            $pdf->SetFont('Arial', 'B', 14);
            $amountText = 'Montant: ' . $this->formatAmount() . ' ' . $this->currency;
            $pdf->Cell(0, 10, $amountText, 0, 1);
            $pdf->Ln(5);
            
            $pdf->SetFont('Arial', '', 10);
            $generatedText = 'Généré le: ' . $this->generatedAt->format('Y-m-d H:i');
            $pdf->Cell(0, 8, $generatedText, 0, 1);
            
            return $pdf->Output('S');
        }
    }
?>