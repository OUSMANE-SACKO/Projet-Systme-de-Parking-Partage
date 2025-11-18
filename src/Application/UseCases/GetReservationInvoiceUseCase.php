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
            $schedules = $parking->getPricingSchedules();
            $start = $reservation->getStartTime();
            $end = $reservation->getEndTime();
            $durationHours = max(0.0, ($end->getTimestamp() - $start->getTimestamp()) / 3600);

            if (empty($schedules)) {
                if ($applyPenalty) {
                    return round(20 + ($durationHours * 1.0), 2);
                }
                return round($durationHours * 1.0, 2);
            }

            $previous = null;
            $next = null;
            foreach ($schedules as $schedule) {
                $time = $schedule->getTime();
                if ($time <= $start) {
                    if ($previous === null || $time > $previous->getTime()) {
                        $previous = $schedule;
                    }
                } elseif ($next === null || $time < $next->getTime()) {
                    $next = $schedule;
                }
            }
            if (!$applyPenalty) {
                $basePrice = $previous ? $previous->getPrice() : $schedules[0]->getPrice();
                return round($durationHours * $basePrice, 2);
            }

            $priceSchedule = $next ?? $previous ?? $schedules[0];
            $amount = 20 + ($durationHours * $priceSchedule->getPrice());
            return round($amount, 2);
        }
    }
?>