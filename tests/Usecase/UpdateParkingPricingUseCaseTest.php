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

    public function testExecuteWithValidPricingSchedules(): void
    {
        $mockSchedule1 = $this->createMock(PricingSchedule::class);
        $mockSchedule2 = $this->createMock(PricingSchedule::class);
        $pricingSchedules = [$mockSchedule1, $mockSchedule2];

        $this->mockParking->expects($this->once())
            ->method('setPricingSchedules')
            ->with($pricingSchedules);

        $this->updateParkingPricingUseCase->execute($this->mockParking, $pricingSchedules);
    }

    public function testExecuteWithEmptyPricingSchedules(): void
    {
        $pricingSchedules = [];

        $this->mockParking->expects($this->once())
            ->method('setPricingSchedules')
            ->with($pricingSchedules);

        $this->updateParkingPricingUseCase->execute($this->mockParking, $pricingSchedules);
    }

    public function testExecuteWithInvalidPricingSchedule(): void
    {
        $pricingSchedules = ['not_a_pricing_schedule'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of PricingSchedule');

        $this->updateParkingPricingUseCase->execute($this->mockParking, $pricingSchedules);
    }

    public function testExecuteWithMixedValidAndInvalidSchedules(): void
    {
        $mockSchedule = $this->createMock(PricingSchedule::class);
        $pricingSchedules = [$mockSchedule, 'invalid_schedule'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of PricingSchedule');

        $this->updateParkingPricingUseCase->execute($this->mockParking, $pricingSchedules);
    }

    public function testAddPricingSchedule(): void
    {
        $time = new DateTime('08:00:00');
        $price = 2.5;

        $this->mockParking->expects($this->once())
            ->method('addPricingSchedule')
            ->with($this->isInstanceOf(PricingSchedule::class));

        $result = $this->updateParkingPricingUseCase->addPricingSchedule($this->mockParking, $time, $price);

        $this->assertInstanceOf(PricingSchedule::class, $result);
    }

    public function testRemovePricingSchedule(): void
    {
        $mockSchedule = $this->createMock(PricingSchedule::class);

        $this->mockParking->expects($this->once())
            ->method('removePricingSchedule')
            ->with($mockSchedule)
            ->willReturn(true);

        $result = $this->updateParkingPricingUseCase->removePricingSchedule($this->mockParking, $mockSchedule);

        $this->assertTrue($result);
    }

    public function testRemovePricingScheduleNotFound(): void
    {
        $mockSchedule = $this->createMock(PricingSchedule::class);

        $this->mockParking->expects($this->once())
            ->method('removePricingSchedule')
            ->with($mockSchedule)
            ->willReturn(false);

        $result = $this->updateParkingPricingUseCase->removePricingSchedule($this->mockParking, $mockSchedule);

        $this->assertFalse($result);
    }

    public function testClearAllPricingSchedules(): void
    {
        $this->mockParking->expects($this->once())
            ->method('setPricingSchedules')
            ->with([]);

        $this->updateParkingPricingUseCase->clearAllPricingSchedules($this->mockParking);
    }
}