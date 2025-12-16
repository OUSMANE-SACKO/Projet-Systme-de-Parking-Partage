<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class UpdateParkingPricingUseCaseTest extends TestCase
{
    private UpdateParkingPricingUseCase $updateParkingPricingUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->updateParkingPricingUseCase = new UpdateParkingPricingUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteWithValidPricingTiers(): void
    {
        $mockTier1 = $this->createMock(PricingTier::class);
        $mockTier2 = $this->createMock(PricingTier::class);
        $PricingTiers = [$mockTier1, $mockTier2];

        $this->mockParking->expects($this->once())
            ->method('setPricingTiers')
            ->with($PricingTiers);

        $this->updateParkingPricingUseCase->execute($this->mockParking, $PricingTiers);
    }

    public function testExecuteWithEmptyPricingTiers(): void
    {
        $PricingTiers = [];

        $this->mockParking->expects($this->once())
            ->method('setPricingTiers')
            ->with($PricingTiers);

        $this->updateParkingPricingUseCase->execute($this->mockParking, $PricingTiers);
    }

    public function testExecuteWithInvalidPricingTier(): void
    {
        $PricingTiers = ['not_a_pricing_schedule'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of PricingTier');

        $this->updateParkingPricingUseCase->execute($this->mockParking, $PricingTiers);
    }

    public function testExecuteWithMixedValidAndInvalidSchedules(): void
    {
        $mockTier = $this->createMock(PricingTier::class);
        $PricingTiers = [$mockTier, 'invalid_schedule'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of PricingTier');

        $this->updateParkingPricingUseCase->execute($this->mockParking, $PricingTiers);
    }

    public function testAddPricingTier(): void
    {
        $time = new DateTime('08:00:00');
        $price = 2.5;

        $this->mockParking->expects($this->once())
            ->method('addPricingTier')
            ->with($this->isInstanceOf(PricingTier::class));

        $result = $this->updateParkingPricingUseCase->addPricingTier($this->mockParking, $time, $price);

        $this->assertInstanceOf(PricingTier::class, $result);
    }

    public function testRemovePricingTier(): void
    {
        $mockTier = $this->createMock(PricingTier::class);

        $this->mockParking->expects($this->once())
            ->method('removePricingTier')
            ->with($mockTier)
            ->willReturn(true);

        $result = $this->updateParkingPricingUseCase->removePricingTier($this->mockParking, $mockTier);

        $this->assertTrue($result);
    }

    public function testRemovePricingTierNotFound(): void
    {
        $mockTier = $this->createMock(PricingTier::class);

        $this->mockParking->expects($this->once())
            ->method('removePricingTier')
            ->with($mockTier)
            ->willReturn(false);

        $result = $this->updateParkingPricingUseCase->removePricingTier($this->mockParking, $mockTier);

        $this->assertFalse($result);
    }

    public function testClearAllPricingTiers(): void
    {
        $this->mockParking->expects($this->once())
            ->method('setPricingTiers')
            ->with([]);

        $this->updateParkingPricingUseCase->clearAllPricingTiers($this->mockParking);
    }
}
