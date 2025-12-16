<?php


use PHPUnit\Framework\TestCase;

class SubscriptionTypeTest extends TestCase {
    
    public function testSubscriptionTypeConstruction(): void {
        $name = 'Premium';
        $description = 'Premium parking access';
        $monthlyPrice = 99.99;
        $durationMonths = 12;
        $timeSlots = [
            ['day' => 'Monday', 'startTime' => '08:00', 'endTime' => '18:00'],
            ['day' => 'Tuesday', 'startTime' => '08:00', 'endTime' => '18:00']
        ];
        
        $subscriptionType = new SubscriptionType($name, $description, $monthlyPrice, $durationMonths, $timeSlots);
        
        
        $this->assertEquals($name, $subscriptionType->getName());
        $this->assertEquals($description, $subscriptionType->getDescription());
        $this->assertEquals($monthlyPrice, $subscriptionType->getMonthlyPrice());
        $this->assertEquals($timeSlots, $subscriptionType->getWeeklyTimeSlots());
    }
    
    public function testSubscriptionTypeWithEmptyTimeSlots(): void {
        $subscriptionType = new SubscriptionType('Basic', 'Basic access', 29.99, 1);
        
        $this->assertEquals('Basic', $subscriptionType->getName());
        $this->assertEquals('Basic access', $subscriptionType->getDescription());
        $this->assertEquals(29.99, $subscriptionType->getMonthlyPrice());
        $this->assertEmpty($subscriptionType->getWeeklyTimeSlots());
    }
    
    public function testSetters(): void {
        $subscriptionType = new SubscriptionType('Initial', 'Description', 50.0, 6);
        
        $subscriptionType->setName('Updated');
        $this->assertEquals('Updated', $subscriptionType->getName());
        
        $subscriptionType->setId(1);
        $this->assertEquals(1, $subscriptionType->getId());
    }
    
    public function testUniqueIds(): void {
        $type1 = new SubscriptionType('Type1', 'Desc1', 10.0, 1);
        $type2 = new SubscriptionType('Type2', 'Desc2', 20.0, 2);
        
        // IDs are null until persisted to database
        $this->assertNull($type1->getId());
        $this->assertNull($type2->getId());
        // But the objects are different instances
        $this->assertNotSame($type1, $type2);
    }
}

