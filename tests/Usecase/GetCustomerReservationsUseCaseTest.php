<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetCustomerReservationsUseCaseTest extends TestCase
{
    public function testExecuteAllScenarios(): void
    {
        $scenarios = [
            'no_reservations' => ['reservations' => [], 'expected_count' => 0, 'description' => 'empty parking'],
            'all_matching' => ['reservations' => [true, true], 'expected_count' => 2, 'description' => 'all reservations match'],
            'mixed_reservations' => ['reservations' => [true, false, true], 'expected_count' => 2, 'description' => 'mixed ownership'],
            'no_matches' => ['reservations' => [false, false], 'expected_count' => 0, 'description' => 'no matching reservations'],
            'single_match' => ['reservations' => [false, true], 'expected_count' => 1, 'description' => 'single match'],
            'order_preservation' => ['reservations' => [true, false, true, true], 'expected_count' => 3, 'description' => 'order preserved']
        ];

        foreach ($scenarios as $name => $scenario) {
            $useCase = new GetCustomerReservationsUseCase();
            $mockCustomer = $this->createMock(Customer::class);
            $mockParking = $this->createMock(Parking::class);
            
            $allReservations = [];
            $expectedReservations = [];
            
            foreach ($scenario['reservations'] as $index => $belongsToCustomer) {
                $mockReservation = $this->createMock(Reservation::class);
                
                if ($belongsToCustomer) {
                    $mockReservation->method('getCustomer')->willReturn($mockCustomer);
                    $expectedReservations[] = $mockReservation;
                } else {
                    $otherCustomer = $this->createMock(Customer::class);
                    $mockReservation->method('getCustomer')->willReturn($otherCustomer);
                }
                
                $allReservations[] = $mockReservation;
            }
            
            $mockParking->method('getReservations')->willReturn($allReservations);
            $result = $useCase->execute($mockCustomer, $mockParking);
            
            $this->assertIsArray($result, "Result should be array for scenario: {$scenario['description']}");
            $this->assertCount($scenario['expected_count'], $result, "Count mismatch for scenario: {$scenario['description']}");
            
            // Verify order and exact objects
            foreach ($expectedReservations as $index => $expectedReservation) {
                $this->assertSame($expectedReservation, $result[$index], "Order/object mismatch at index $index for scenario: {$scenario['description']}");
            }
        }
    }

    public function testExecuteWithLargeDataset(): void
    {
        $useCase = new GetCustomerReservationsUseCase();
        $mockCustomer = $this->createMock(Customer::class);
        $mockParking = $this->createMock(Parking::class);
        
        $reservations = [];
        $expectedCount = 0;
        
        for ($i = 0; $i < 100; $i++) {
            $mockReservation = $this->createMock(Reservation::class);
            
            if ($i % 3 === 0) {
                $mockReservation->method('getCustomer')->willReturn($mockCustomer);
                $expectedCount++;
            } else {
                $otherCustomer = $this->createMock(Customer::class);
                $mockReservation->method('getCustomer')->willReturn($otherCustomer);
            }
            
            $reservations[] = $mockReservation;
        }
        
        $mockParking->method('getReservations')->willReturn($reservations);
        $result = $useCase->execute($mockCustomer, $mockParking);
        
        $this->assertIsArray($result);
        $this->assertCount($expectedCount, $result, 'Large dataset filtering failed');
    }
}
