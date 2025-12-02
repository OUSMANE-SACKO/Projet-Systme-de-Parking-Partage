<?php
    class GetReservationInvoiceUseCase {
        /**
         * @param Reservation
         * @param string
         * @param bool
         * @return array
         */
        public function execute(Reservation $reservation, string $format = 'html', bool $applyPenalty = false): array {
            $amount = $this->computeAmount($reservation, $applyPenalty);
            $invoice = new Invoice($reservation, $amount, 'EUR');

            if ($format === 'pdf') {
                $output = $invoice->toPdfBinary();
            } else {
                $output = $invoice->toHtml();
            }

            return [
                'invoice' => $invoice,
                'output' => $output,
                'format' => $format,
            ];
        }

        private function computeAmount(Reservation $reservation, bool $applyPenalty): float {
            $parking = $reservation->getParking();
            $tiers = $parking->getPricingTiers();
            $start = $reservation->getStartTime();
            $end = $reservation->getEndTime();
            $durationHours = max(0.0, ($end->getTimestamp() - $start->getTimestamp()) / 3600);

            if (empty($tiers)) {
                if ($applyPenalty) {
                    return round(20 + ($durationHours * 1.0), 2);
                }
                return round($durationHours * 1.0, 2);
            }

            $previous = null;
            $next = null;
            foreach ($tiers as $tier) {
                $time = $tier->getTime();
                if ($time <= $start) {
                    if ($previous === null || $time > $previous->getTime()) {
                        $previous = $tier;
                    }
                } elseif ($next === null || $time < $next->getTime()) {
                    $next = $tier;
                }
            }
            if (!$applyPenalty) {
                $basePrice = $previous ? $previous->getPrice() : $tiers[0]->getPrice();
                return round($durationHours * $basePrice, 2);
            }

            $priceTier = $next ?? $previous ?? $tiers[0];
            $amount = 20 + ($durationHours * $priceTier->getPrice());
            return round($amount, 2);
        }
    }
?>