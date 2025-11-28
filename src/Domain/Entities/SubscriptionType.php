<?php
    class SubscriptionType {
        private string $id;
        private string $name;             
        private string $description;        
        private float $monthlyPrice;
        private int $durationMonths;
        private array $weeklyTimeSlots;

        public function __construct(string $name, string $description, float $monthlyPrice, int $durationMonths, array $weeklyTimeSlots = []) {
            // weeklyTimeSlots structure (opening hours by day):
            // [ ['day' => 'Monday'..'Sunday', 'startTime' => 'HH:MM', 'endTime' => 'HH:MM'], ... ]
            $this->id = uniqid('', true);
            $this->name = $name;
            $this->description = $description;
            $this->monthlyPrice = $monthlyPrice;
            $this->durationMonths = $durationMonths;
            $this->weeklyTimeSlots = $weeklyTimeSlots;
        }
        
        //Getter
        public function getId(): string { 
            return $this->id;
        }

        public function getName(): string { 
            return $this->name;
        }

        public function getDescription(): string { 
            return $this->description; 
        }

        public function getMonthlyPrice(): float { 
            return $this->monthlyPrice; 
        }

        public function getDurationMonths(): int {
            return $this->durationMonths;
        }

        public function getWeeklyTimeSlots(): array { 
            return $this->weeklyTimeSlots; 
        }

        //Setter
        public function setName(string $name): void { 
            $this->name = $name; 
        }
    }
?>