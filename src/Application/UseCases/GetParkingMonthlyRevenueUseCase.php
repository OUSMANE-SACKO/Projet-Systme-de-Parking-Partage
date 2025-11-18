<?php
    class GetParkingMonthlyRevenueUseCase {
        /**
         * @param Parking
         * @param int
         * @param int
         * @return array
         */

        public function execute(Parking $parking, int $month, int $year): array {
            $reservationsRevenue = $this->computeReservationsRevenue($parking, $month, $year);
            $subscriptionsRevenue = $this->computeSubscriptionsRevenue($parking, $month, $year);
            
            return [
                'month' => sprintf('%04d-%02d', $year, $month),
                'reservationsRevenue' => round($reservationsRevenue, 2),
                'subscriptionsRevenue' => round($subscriptionsRevenue, 2),
                'totalRevenue' => round($reservationsRevenue + $subscriptionsRevenue, 2),
            ];
        }

        private function computeReservationsRevenue(Parking $parking, int $month, int $year): float {
            $revenue = 0.0;
            $monthStart = new DateTime("$year-$month-01 00:00:00");
            $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);

            foreach ($parking->getReservations() as $reservation) {
                $endTime = $reservation->getEndTime();
                // Réservation terminée dans le mois
                if ($endTime >= $monthStart && $endTime <= $monthEnd) {
                    $invoiceUseCase = new GetReservationInvoiceUseCase();
                    $invoiceData = $invoiceUseCase->execute($reservation, 'html', false);
                    $revenue += $invoiceData['invoice']->getAmount();
                }
            }

            return $revenue;
        }

        private function computeSubscriptionsRevenue(Parking $parking, int $month, int $year): float {
            $revenue = 0.0;
            $monthStart = new DateTime("$year-$month-01 00:00:00");
            $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);

            // Récupérer tous les abonnements des clients pour ce parking
            foreach ($parking->getParkingSpaces() as $space) {
                $customer = $space->getCustomer();
                if ($customer === null) continue;

                foreach ($customer->getSubscriptions() as $subscription) {
                    // Vérifier si l'abonnement est actif pendant le mois
                    $subStart = $subscription->getStartDate();
                    $subEnd = $subscription->getEndDate();
                    
                    // L'abonnement chevauche le mois si: début <= fin_mois ET fin >= début_mois
                    if ($subStart <= $monthEnd && $subEnd >= $monthStart) {
                        $monthlyPrice = $subscription->getSubscriptionType()->getMonthlyPrice();
                        $revenue += $monthlyPrice;
                    }
                }
            }

            return $revenue;
        }
    }
?>