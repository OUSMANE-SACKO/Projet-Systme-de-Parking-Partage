<?php
    class Subscription {
        private string $id;
        private Customer $customer;
        private DateTime $startDate;
        private DateTime $endDate;
        
        /** @var array */
        private array $weeklyTimeSlots = [];

        public function __construct(Customer $customer, DateTime $startDate, DateTime $endDate, array $weeklyTimeSlots = []) {
            if ($endDate < $startDate) {
                throw new InvalidArgumentException('endDate must be after startDate');
            }
            
            $interval = $startDate->diff($endDate);
            $totalMonths = ($interval->y * 12) + $interval->m;
            
            if ($totalMonths < 1) {
                throw new InvalidArgumentException('Subscription duration must be at least 1 month');
            }
            
            if ($totalMonths > 12) {
                throw new InvalidArgumentException('Subscription duration cannot exceed 1 year');
            }
            
            $this->id = uniqid('sub_', true);
            $this->customer = $customer;
            $this->startDate = $startDate;
            $this->endDate = $endDate;
            $this->weeklyTimeSlots = $weeklyTimeSlots;
        }
        
        //getters
        public function getId(): string {
            return $this->id;
        }

        public function getCustomer(): Customer {
            return $this->customer;
        }

        public function getStartDate(): DateTime {
            return $this->startDate;
        }

        public function getEndDate(): DateTime {
            return $this->endDate;
        }

        public function getWeeklyTimeSlots(): array {
            return $this->weeklyTimeSlots;
        }

        //setters
        public function setCustomer(Customer $customer): void {
            $this->customer = $customer;
        }

        public function setStartDate(DateTime $startDate): void {
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

        public function setEndDate(DateTime $endDate): void {
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

        public function setWeeklyTimeSlots(array $weeklyTimeSlots): void {
            $this->weeklyTimeSlots = $weeklyTimeSlots;
        }

        // Helper
        public function addTimeSlot(string $dayOfWeek, string $startTime, string $endTime): void {
            $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            $this->weeklyTimeSlots[] = [
                'day' => $dayOfWeek,
                'startTime' => $startTime,
                'endTime' => $endTime
            ];
        }

        public function removeTimeSlot(int $index): bool {
            if (!isset($this->weeklyTimeSlots[$index])) {
                return false;
            }
            array_splice($this->weeklyTimeSlots, $index, 1);
            return true;
        }
        
        public function removeAllTimeSlots(): void {
            $this->weeklyTimeSlots = [];
        }
        
        public function isActiveAt(DateTime $dateTime): bool {
            if ($dateTime < $this->startDate || $dateTime > $this->endDate) {
                return false;
            }
            
            if (empty($this->weeklyTimeSlots)) {
                return true;
            }
            
            $dayOfWeek = $dateTime->format('l');
            $currentTime = $dateTime->format('H:i');
            
            foreach ($this->weeklyTimeSlots as $slot) {
                if ($slot['day'] === $dayOfWeek) {
                    if ($slot['endTime'] < $slot['startTime']) {
                        if ($currentTime >= $slot['startTime'] || $currentTime <= $slot['endTime']) {
                            return true;
                        }
                    } else {
                        if ($currentTime >= $slot['startTime'] && $currentTime <= $slot['endTime']) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }
        
        public function isFullAccess(): bool {
            return empty($this->weeklyTimeSlots);
        }
        
        public function getDurationInMonths(): int {
            $interval = $this->startDate->diff($this->endDate);
            return ($interval->y * 12) + $interval->m;
        }
    }
?>