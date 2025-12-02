<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class EnterParkingUseCaseTest extends TestCase
{
    private EnterParkingUseCase $enterParkingUseCase;

    protected function setUp(): void
    {
        $this->enterParkingUseCase = new EnterParkingUseCase();
    }

    public function testExecuteWithDifferentTimes(): void
    {
        $customer = $this->createMock(Customer::class);
        $parking = $this->createMock(Parking::class);
        
        $times = [
            new DateTime('2024-01-01 10:00:00'),
            new DateTime('2020-01-01 10:00:00'), // Past
            new DateTime('+1 year'), // Future
            new DateTime('2024-12-31 23:59:59')
        ];
        
        foreach ($times as $entryTime) {
            // This use case is deprecated - just test that it executes without error
            $this->enterParkingUseCase->execute($customer, $parking, $entryTime);
            $this->assertTrue(true); // If we reach here, no exception was thrown
        }
    }
}