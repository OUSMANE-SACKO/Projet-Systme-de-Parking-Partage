<?php
    class Subscription {
        private string $id;
        private Customer $customer;
        private Parking $parking;
        private DateTime $startDate;
        private DateTime $endDate;
        
        /** @var array */
        private array $weeklyTimeSlots = [];

        public function __construct(Customer $customer, Parking $parking, DateTime $startDate, DateTime $endDate, array $weeklyTimeSlots = []) {
            if ($endDate < $startDate) {
                throw new InvalidArgumentException('endDate must be after startDate');
            }
            
            // Vérifier la durée minimale (1 mois) et maximale (1 an)
            $interval = $startDate->diff($endDate);
            $totalMonths = ($interval->y * 12) + $interval->m;
            
            if ($totalMonths < 1) {
                throw new InvalidArgumentException('Subscription duration must be at least 1 month');
            }
            
            if ($totalMonths > 12) {
                throw new InvalidArgumentException('Subscription duration cannot exceed 1 year');
            }
            
            $this->id = uniqid('', true);
            $this->customer = $customer;
            $this->parking = $parking;
            $this->startDate = $startDate;
            $this->endDate = $endDate;
            $this->weeklyTimeSlots = $weeklyTimeSlots;
        }
        
        //getters
        public function getId() : string {
            return $this->id;
        }

        public function getCustomer() : Customer {
            return $this->customer;
        }

        public function getParking() : Parking {
            return $this->parking;
        }

        public function getStartDate() : DateTime {
            return $this->startDate;
        }

        public function getEndDate() : DateTime {
            return $this->endDate;
        }

        public function getWeeklyTimeSlots() : array {
            return $this->weeklyTimeSlots;
        }

        //setters
        public function setCustomer(Customer $customer) : void {
            $this->customer = $customer;
        }

        public function setParking(Parking $parking) : void {
            $this->parking = $parking;
        }

        public function setStartDate(DateTime $startDate) : void {
            if ($this->endDate < $startDate) {
                throw new InvalidArgumentException('startDate must be before endDate');
            }
            
            $interval = $startDate->diff($this->endDate);
            $totalMonths = ($interval->y * 12) + $interval->m;
            
            if ($totalMonths < 1) {
                throw new InvalidArgumentException('Subscription duration must be at least 1 month');
            }
            
            $this->startDate = $startDate;
        }

        public function setEndDate(DateTime $endDate) : void {
            if ($endDate < $this->startDate) {
                throw new InvalidArgumentException('endDate must be after startDate');
            }
            
            $interval = $this->startDate->diff($endDate);
            $totalMonths = ($interval->y * 12) + $interval->m;
            
            if ($totalMonths < 1) {
                throw new InvalidArgumentException('Subscription duration must be at least 1 month');
            }
            
            if ($totalMonths > 12) {
                throw new InvalidArgumentException('Subscription duration cannot exceed 1 year');
            }
            
            $this->endDate = $endDate;
        }

        public function setWeeklyTimeSlots(array $weeklyTimeSlots) : void {
            $this->weeklyTimeSlots = $weeklyTimeSlots;
        }

        // Helper methods
        public function addTimeSlot(string $dayOfWeek, string $startTime, string $endTime) : void {
            $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            if (!in_array($dayOfWeek, $validDays)) {
                throw new InvalidArgumentException('Invalid day of week');
            }
            
            $this->weeklyTimeSlots[] = [
                'day' => $dayOfWeek,
                'startTime' => $startTime,
                'endTime' => $endTime
            ];
        }

        public function removeTimeSlot(int $index) : bool {
            if (!isset($this->weeklyTimeSlots[$index])) {
                return false;
            }
            array_splice($this->weeklyTimeSlots, $index, 1);
            return true;
        }
    }
?>