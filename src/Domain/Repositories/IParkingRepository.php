<?php
    interface IParkingRepository {
        /**
         * @param int $id
         * @return Parking|null
         */
        public function findById(int $id): ?Parking;

        /**
         * @param Parking $parking
         * @return void
         */
        public function save(Parking $parking): void;

        /**
         * @return Parking[]
         */
        public function findAll(): array;

        /**
         * @param float $latitude
         * @param float $longitude
         * @param float $radiusKm
         * @return Parking[]
         */
        public function findByLocation(float $latitude, float $longitude, float $radiusKm): array;

        /**
         * @param int $ownerId
         * @return Parking[]
         */
        public function findByOwnerId(int $ownerId): array;
    }
?>