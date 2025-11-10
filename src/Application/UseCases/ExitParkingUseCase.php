<?php
    class ParkingCheckOutUseCase {
        public function execute(ParkingSpace $parkingSpace, DateTime $exitTime): void {
            $parkingSpace->setEndTime($exitTime);
        }
    }
?>