<?php

use PHPUnit\Framework\TestCase;

class SubscriptionTest extends TestCase {
    private Customer $customer;
    private SubscriptionType $type;
    private DateTime $start;
    private DateTime $end;
    private Subscription $subscription;
    
    protected function setUp(): void {
        $this->customer = new Customer('Doe', 'John', 'john@test.com', 'hash123');
        $this->type = new SubscriptionType('Monthly', 'Monthly access', 50.0, 1);
        $this->start = new DateTime('2024-01-01');
        $this->end = new DateTime('2024-02-01');
        $this->subscription = new Subscription($this->customer, $this->start, $this->end, $this->type, 1);
    }
    
    /**
     * Test construction, getters, and duration calculation
     */
    public function testConstructionAndGetters(): void {
        // Test all getters and unique ID
        $this->assertNotEmpty($this->subscription->getId());
        $this->assertSame($this->customer, $this->subscription->getCustomer());
        $this->assertEquals($this->start, $this->subscription->getStartDate());
        $this->assertEquals($this->end, $this->subscription->getEndDate());
        $this->assertSame($this->type, $this->subscription->getSubscriptionType());
        $this->assertEquals(1, $this->subscription->getDurationMonths());
        $this->assertEquals(1, $this->subscription->getDurationInMonths());
        
        // Test unique IDs
        $other = new Subscription($this->customer, $this->start, $this->end, $this->type, 1);
        $this->assertNotEquals($this->subscription->getId(), $other->getId());
    }
    
    /**
     * Test all constructor validations
     */
    public function testConstructorValidations(): void {
        // Test end before start
        try {
            new Subscription($this->customer, $this->end, $this->start, $this->type, 1);
            $this->fail('Expected exception');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('endDate must be after startDate', $e->getMessage());
        }
        
        // Test duration too short (< 1 month)
        try {
            new Subscription($this->customer, new DateTime('2024-01-01'), new DateTime('2024-01-15'), $this->type, 1);
            $this->fail('Expected exception');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Subscription duration must be at least 1 month', $e->getMessage());
        }
        
        // Test duration too long (> 12 months)
        try {
            new Subscription($this->customer, new DateTime('2024-01-01'), new DateTime('2025-02-01'), $this->type, 13);
            $this->fail('Expected exception');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Subscription duration cannot exceed 1 year', $e->getMessage());
        }
    }
    
    /**
     * Test all setters with valid and invalid values
     */
    public function testSetters(): void {
        $newCustomer = new Customer('Smith', 'Jane', 'jane@test.com', 'hash456');
        
        // Test valid setters
        $this->subscription->setCustomer($newCustomer);
        $this->subscription->setStartDate(new DateTime('2023-12-01'));
        $this->subscription->setEndDate(new DateTime('2024-03-01'));
        $this->subscription->setDurationMonths(6);
        
        $this->assertSame($newCustomer, $this->subscription->getCustomer());
        $this->assertEquals(new DateTime('2023-12-01'), $this->subscription->getStartDate());
        $this->assertEquals(new DateTime('2024-03-01'), $this->subscription->getEndDate());
        $this->assertEquals(6, $this->subscription->getDurationMonths());
    }
    
    /**
     * Test setter validations for all validation rules
     */
    public function testSetterValidations(): void {
        $errorCases = [
            // setStartTime validations
            ['setStartDate', [new DateTime('2024-03-01')], 'startDate must be before endDate'],
            ['setStartDate', [new DateTime('2024-01-20')], 'Subscription duration must be at least 1 month'],
            
            // setEndDate validations
            ['setEndDate', [new DateTime('2023-12-01')], 'endDate must be after startDate'],
            ['setEndDate', [new DateTime('2024-01-10')], 'Subscription duration must be at least 1 month'],
            ['setEndDate', [new DateTime('2025-02-01')], 'Subscription duration cannot exceed 1 year'],
            
            // setDurationMonths validations
            ['setDurationMonths', [0], 'Subscription duration must be at least 1 month'],
            ['setDurationMonths', [-1], 'Subscription duration must be at least 1 month'],
            ['setDurationMonths', [13], 'Subscription duration cannot exceed 1 year'],
        ];
        
        foreach ($errorCases as [$method, $args, $expectedMessage]) {
            try {
                $this->subscription->$method(...$args);
                $this->fail("Expected InvalidArgumentException for $method");
            } catch (InvalidArgumentException $e) {
                $this->assertEquals($expectedMessage, $e->getMessage());
            }
        }
    }
    
    /**
     * Test duration calculation edge cases and boundary values
     */
    public function testDurationCalculationEdgeCases(): void {
        $testCases = [
            ['2024-01-01', '2024-02-01', 1],   // Exactly 1 month
            ['2024-01-01', '2024-04-01', 3],   // 3 months
            ['2024-01-01', '2024-07-01', 6],   // 6 months
            ['2024-01-01', '2025-01-01', 12],  // Exactly 12 months (max)
        ];
        
        foreach ($testCases as [$startDate, $endDate, $expectedMonths]) {
            $sub = new Subscription($this->customer, new DateTime($startDate), new DateTime($endDate), $this->type, $expectedMonths);
            $this->assertEquals($expectedMonths, $sub->getDurationInMonths());
        }
        
        // Test boundary for setDurationMonths (1-12 valid range)
        for ($months = 1; $months <= 12; $months++) {
            $this->subscription->setDurationMonths($months);
            $this->assertEquals($months, $this->subscription->getDurationMonths());
        }
    }
}
