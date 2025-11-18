<?php
    class ParkingCheckInUseCase {
        public function execute(Customer $customer, Parking $parking, DateTime $entryTime): ParkingSpace {
            // Crée un nouveau stationnement (ParkingSpace)
            $parkingSpace = new ParkingSpace($customer, $entryTime, $parking);

            // Ajoute le stationnement à la liste du parking
            $parking->addParkingSpace($parkingSpace);

            // Ajoute le stationnement à la liste du client
            $customer->addParkingSpace($parkingSpace);

            return $parkingSpace;
        }
    }
?>