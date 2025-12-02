<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetCustomerParkingSpacesUseCaseTest extends TestCase
{
    private GetCustomerParkingSpacesUseCase $useCase;
    private MockObject $mockCustomer;

    protected function setUp(): void
    {
        $this->useCase = new GetCustomerParkingSpacesUseCase();
        $this->mockCustomer = $this->createMock(Customer::class);
    }

    public function testExecute(): void
    {
        // Test multiple scenarios efficiently
        $scenarios = [
            'empty' => [],
            'single' => [$this->createMock(ParkingSpace::class)],
            'multiple' => [
                $this->createMock(ParkingSpace::class),
                $this->createMock(ParkingSpace::class),
                $this->createMock(ParkingSpace::class)
            ]
        ];
        
        foreach ($scenarios as $name => $spaces) {
            // Create a fresh customer mock for each scenario
            $freshCustomer = $this->createMock(Customer::class);
            $freshCustomer->method('getParkingSpaces')->willReturn($spaces);
            $result = $this->useCase->execute($freshCustomer);
            
            $this->assertSame($spaces, $result, "Failed for scenario: {$name}");
            $this->assertCount(count($spaces), $result, "Count mismatch for: {$name}");
        }
    }
}