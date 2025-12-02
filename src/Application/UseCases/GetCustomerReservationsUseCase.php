<?php
    class GetCustomerReservationsUseCase {
        /**
         * Retourne les réservations d'un client pour un parking donné.
         * @return Reservation[]
         */
        public function execute(Customer $customer, Parking $parking): array {
            $result = [];
            foreach ($parking->getReservations() as $reservation) {
                if ($reservation->getCustomer() === $customer) {
                    $result[] = $reservation;
                }
            }
            return $result;
        }
    }
?>