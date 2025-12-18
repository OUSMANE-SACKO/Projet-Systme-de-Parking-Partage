<?php
    interface IReservationRepository {
        /**
         * @param string $id
         * @return Reservation|null
         */
        public function findById(string $id): ?Reservation;

        /**
         * @param Reservation $reservation
         * @return void
         */
        public function save(Reservation $reservation): void;

        /**
         * @param string $customerId
         * @return Reservation[]
         */
        public function findByCustomerId(string $customerId): array;

        /**
         * @param string $parkingId
         * @return Reservation[]
         */
        public function findByParkingId(string $parkingId): array;

        /**
         * @param string $parkingId
         * @param DateTime $startTime
         * @param DateTime $endTime
         * @return Reservation[]
         */
        public function findByParkingAndTimeRange(string $parkingId, DateTime $startTime, DateTime $endTime): array;
    }
?>