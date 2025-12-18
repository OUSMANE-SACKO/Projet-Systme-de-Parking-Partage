<?php
    class GetCustomerParkingSpacesUseCase {
        public function execute(Customer $customer): array {
            return $customer->getParkingSpaces();
        }
    }
?>