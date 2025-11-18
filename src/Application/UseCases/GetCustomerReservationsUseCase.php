<?php
    class GetCustomerReservationsUseCase {
        /**
         * @return Reservation[]
         */
        public function execute(Customer $customer): array {
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