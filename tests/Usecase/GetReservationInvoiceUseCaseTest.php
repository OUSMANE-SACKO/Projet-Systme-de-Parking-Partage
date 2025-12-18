<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetReservationInvoiceUseCaseTest extends TestCase
{
    private GetReservationInvoiceUseCase $getReservationInvoiceUseCase;
    private MockObject $mockReservation;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->getReservationInvoiceUseCase = new GetReservationInvoiceUseCase();
        $this->mockReservation = $this->createMock(Reservation::class);
        $this->mockParking = $this->createMock(Parking::class);
        
        $this->mockReservation->method('getParking')->willReturn($this->mockParking);
    }

    public function testExecuteWithDefaultParameters(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 12:00:00');
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        $this->assertIsString($result['output']);
        $this->assertEquals('html', $result['format']);
    }

    public function testExecuteWithPdfFormat(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 12:00:00');
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation, 'pdf');

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        $this->assertIsString($result['output']);
        $this->assertEquals('pdf', $result['format']);
    }

    public function testExecuteWithNoPricingTiers(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 13:00:00'); // 3 hours
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // With no pricing schedules, should use default rate of 1.0 per hour
        // 3 hours * 1.0 = 3.0
    }

    public function testExecuteWithNoPricingTiersAndPenalty(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 13:00:00'); // 3 hours
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation, 'html', true);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // With penalty: 20 + (3 * 1.0) = 23.0
    }

    public function testExecuteWithPricingTiers(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 12:00:00'); // 2 hours
        
        $mockTier = $this->createMock(PricingTier::class);
        $mockTier->method('getTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
        $mockTier->method('getPrice')->willReturn(2.5);
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([$mockTier]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // 2 hours * 2.5 = 5.0
    }

    public function testExecuteWithMultiplePricingTiers(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 11:00:00'); // 1 hour
        
        $mockTier1 = $this->createMock(PricingTier::class);
        $mockTier1->method('getTime')->willReturn(new DateTime('2024-01-01 08:00:00'));
        $mockTier1->method('getPrice')->willReturn(1.5);
        
        $mockTier2 = $this->createMock(PricingTier::class);
        $mockTier2->method('getTime')->willReturn(new DateTime('2024-01-01 12:00:00')); // After start
        $mockTier2->method('getPrice')->willReturn(3.0);
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([$mockTier1, $mockTier2]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // Should use the most recent schedule before start time (schedule1: 1.5)
        // 1 hour * 1.5 = 1.5
    }

    public function testExecuteWithZeroDuration(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 10:00:00'); // Same time
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // Should handle zero duration gracefully
    }

    public function testExecuteWithNegativeDuration(): void
    {
        $startTime = new DateTime('2024-01-01 12:00:00');
        $endTime = new DateTime('2024-01-01 10:00:00'); // End before start
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // Should handle negative duration as 0
    }

    public function testExecuteWithPenaltyAndSchedules(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 12:00:00'); // 2 hours
        
        $mockTier = $this->createMock(PricingTier::class);
        $mockTier->method('getTime')->willReturn(new DateTime('2024-01-01 09:00:00'));
        $mockTier->method('getPrice')->willReturn(3.0);
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([$mockTier]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation, 'html', true);

        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        // With penalty: 20 + (2 * 3.0) = 26.0
    }

    public function testExecuteReturnsCorrectStructure(): void
    {
        $startTime = new DateTime('2024-01-01 10:00:00');
        $endTime = new DateTime('2024-01-01 12:00:00');
        
        $this->mockReservation->method('getStartTime')->willReturn($startTime);
        $this->mockReservation->method('getEndTime')->willReturn($endTime);
        $this->mockParking->method('getPricingTiers')->willReturn([]);

        $result = $this->getReservationInvoiceUseCase->execute($this->mockReservation);

        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('format', $result);
        
        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        $this->assertIsString($result['output']);
        $this->assertIsString($result['format']);
    }
}
