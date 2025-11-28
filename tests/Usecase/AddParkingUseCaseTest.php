<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class AddParkingUseCaseTest extends TestCase
{
    private AddParkingUseCase $addParkingUseCase;
    private Owner $mockOwner;

    protected function setUp(): void
    {
        $this->addParkingUseCase = new AddParkingUseCase();
        $this->mockOwner = $this->createMock(Owner::class);
    }

    public function testExecuteWithValidParameters(): void
    {
        $location = ['address' => '123 Test Street', 'city' => 'TestCity'];
        $capacity = 50;
        $pricingSchedules = [];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithPricingSchedules(): void
    {
        $location = ['address' => '123 Test Street', 'city' => 'TestCity'];
        $capacity = 50;
        
        $mockPricingSchedule1 = $this->createMock(PricingSchedule::class);
        $mockPricingSchedule2 = $this->createMock(PricingSchedule::class);
        $pricingSchedules = [$mockPricingSchedule1, $mockPricingSchedule2];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithInvalidPricingSchedule(): void
    {
        $location = ['address' => '123 Test Street', 'city' => 'TestCity'];
        $capacity = 50;
        $pricingSchedules = ['invalid_schedule']; // Not a PricingSchedule instance

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All items in pricingSchedules must be instances of PricingSchedule');

        $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);
    }

    public function testExecuteWithEmptyLocation(): void
    {
        $location = [];
        $capacity = 50;
        $pricingSchedules = [];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithZeroCapacity(): void
    {
        $location = ['address' => '123 Test Street', 'city' => 'TestCity'];
        $capacity = 0;
        $pricingSchedules = [];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('capacity must be > 0');

        $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);
    }

    public function testExecuteWithMixedPricingSchedules(): void
    {
        $location = ['address' => '123 Test Street', 'city' => 'TestCity'];
        $capacity = 50;
        
        $mockPricingSchedule = $this->createMock(PricingSchedule::class);
        $pricingSchedules = [$mockPricingSchedule, 'invalid_schedule']; // Mixed array

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All items in pricingSchedules must be instances of PricingSchedule');

        $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $pricingSchedules);
    }
}