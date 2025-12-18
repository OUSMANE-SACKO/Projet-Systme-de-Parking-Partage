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
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $capacity = 50;
        $PricingTiers = [];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithPricingTiers(): void
    {
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $capacity = 50;
        
        $mockPricingTier1 = $this->createMock(PricingTier::class);
        $mockPricingTier2 = $this->createMock(PricingTier::class);
        $PricingTiers = [$mockPricingTier1, $mockPricingTier2];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithInvalidPricingTier(): void
    {
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $capacity = 50;
        $PricingTiers = ['invalid_schedule']; // Not a PricingTier instance

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All items in PricingTiers must be instances of PricingTier');

        $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);
    }

    public function testExecuteWithEmptyLocation(): void
    {
        $location = ['latitude' => 0.0, 'longitude' => 0.0];
        $capacity = 50;
        $PricingTiers = [];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);

        $this->assertInstanceOf(Parking::class, $result);
    }

    public function testExecuteWithZeroCapacity(): void
    {
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $capacity = 0;
        $PricingTiers = [];

        $this->mockOwner->expects($this->once())
            ->method('addParking')
            ->with($this->isInstanceOf(Parking::class));

        $result = $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);
        
        $this->assertInstanceOf(Parking::class, $result);
        $this->assertEquals(0, $result->getCapacity());
    }

    public function testExecuteWithMixedPricingTiers(): void
    {
        $location = ['latitude' => 48.8566, 'longitude' => 2.3522];
        $capacity = 50;
        
        $mockPricingTier = $this->createMock(PricingTier::class);
        $PricingTiers = [$mockPricingTier, 'invalid_schedule']; // Mixed array

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All items in PricingTiers must be instances of PricingTier');

        $this->addParkingUseCase->execute($this->mockOwner, $location, $capacity, $PricingTiers);
    }
}
