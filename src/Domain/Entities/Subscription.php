<?php
    class Subscription {
        private string $id;
        private Customer $customer;
        private DateTime $startDate;
        private DateTime $endDate;
        private SubscriptionType $subscriptionType;
        private int $durationMonths;

        public function __construct(Customer $customer, DateTime $startDate, DateTime $endDate, SubscriptionType $subscriptionType, int $durationMonths) {
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
            
            $this->id = uniqid('', true);
            $this->customer = $customer;
            $this->startDate = $startDate;
            $this->endDate = $endDate;
            $this->subscriptionType = $subscriptionType;
            $this->durationMonths = $durationMonths;
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

        public function getSubscriptionType(): SubscriptionType {
            return $this->subscriptionType;
        }

        public function getDurationMonths(): int {
            return $this->durationMonths;
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

        public function setDurationMonths(int $durationMonths): void {
            if ($durationMonths < 1) {
                throw new InvalidArgumentException('Subscription duration must be at least 1 month');
            }
            
            if ($durationMonths > 12) {
                throw new InvalidArgumentException('Subscription duration cannot exceed 1 year');
            }
            
            $this->durationMonths = $durationMonths;
        }

        // Helper
        public function getDurationInMonths(): int {
            $interval = $this->startDate->diff($this->endDate);
            return ($interval->y * 12) + $interval->m;
        }
    }
?>