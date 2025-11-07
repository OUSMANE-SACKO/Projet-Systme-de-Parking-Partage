<?php
    class ParkingCheckOutUseCase {
        public function execute(ParkingSpace $parkingSpace, DateTime $exitTime): void {
            // Met à jour la date de sortie
            $parkingSpace->setEndTime($exitTime);

            // Optionnel : vous pouvez ajouter ici la logique de calcul du prix, de libération de la place, etc.
        }
    }
?>