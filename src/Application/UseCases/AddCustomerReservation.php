<?php
    class AddCustomerReservation {
        /**
        * @param Customer
        * @param Parking
        * @param DateTime
        * @param DateTime
        * @return Reservation 
         */
        public function execute(Customer $customer, Parking $parking, DateTime $startTime, DateTime $endTime): Reservation {
            $reservation = new Reservation($customer, $parking, $startTime, $endTime);

            $parking->addReservation($reservation);
            $customer->addReservation($reservation);

            return $reservation;
        }
    }