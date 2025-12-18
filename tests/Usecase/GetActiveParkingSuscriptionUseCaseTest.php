<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetActiveParkingSuscriptionUseCaseTest extends TestCase
{
    private GetActiveParkingSuscriptionUseCase $getParkingSubscriptionsUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->getParkingSubscriptionsUseCase = new GetActiveParkingSuscriptionUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteReturnsAllSubscriptions(): void
    {
        $mockSubscription1 = $this->createMock(Subscription::class);
        $mockSubscription2 = $this->createMock(Subscription::class);
        $expectedSubscriptions = [$mockSubscription1, $mockSubscription2];
        
        $this->mockParking->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn($expectedSubscriptions);

        $result = $this->getParkingSubscriptionsUseCase->execute($this->mockParking);

        $this->assertSame($expectedSubscriptions, $result);
    }

    public function testExecuteWithEmptySubscriptions(): void
    {
        $this->mockParking->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn([]);

        $result = $this->getParkingSubscriptionsUseCase->execute($this->mockParking);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetActiveSubscriptionsWithCurrentSubscriptions(): void
    {
        $now = new DateTime('2024-01-15 12:00:00');
        
        $activeSubscription1 = $this->createMock(Subscription::class);
        $activeSubscription1->method('getStartDate')->willReturn(new DateTime('2024-01-01'));
        $activeSubscription1->method('getEndDate')->willReturn(new DateTime('2024-01-31'));

        $activeSubscription2 = $this->createMock(Subscription::class);
        $activeSubscription2->method('getStartDate')->willReturn(new DateTime('2024-01-10'));
        $activeSubscription2->method('getEndDate')->willReturn(new DateTime('2024-02-10'));

        $expiredSubscription = $this->createMock(Subscription::class);
        $expiredSubscription->method('getStartDate')->willReturn(new DateTime('2023-12-01'));
        $expiredSubscription->method('getEndDate')->willReturn(new DateTime('2023-12-31'));

        $futureSubscription = $this->createMock(Subscription::class);
        $futureSubscription->method('getStartDate')->willReturn(new DateTime('2024-02-01'));
        $futureSubscription->method('getEndDate')->willReturn(new DateTime('2024-02-28'));

        $allSubscriptions = [$activeSubscription1, $activeSubscription2, $expiredSubscription, $futureSubscription];
        
        $this->mockParking->method('getSubscriptions')->willReturn($allSubscriptions);

        $result = $this->getParkingSubscriptionsUseCase->getActiveSubscriptions($this->mockParking, $now);

        $this->assertCount(2, $result);
        $this->assertContains($activeSubscription1, $result);
        $this->assertContains($activeSubscription2, $result);
        $this->assertNotContains($expiredSubscription, $result);
        $this->assertNotContains($futureSubscription, $result);
    }

    public function testGetActiveSubscriptionsWithNoActiveSubscriptions(): void
    {
        $now = new DateTime('2024-01-15 12:00:00'); // Date de référence
        
        $expiredSubscription = $this->createMock(Subscription::class);
        $expiredSubscription->method('getStartDate')->willReturn(new DateTime('2023-01-01'));
        $expiredSubscription->method('getEndDate')->willReturn(new DateTime('2023-12-31'));

        $futureSubscription = $this->createMock(Subscription::class);
        $futureSubscription->method('getStartDate')->willReturn(new DateTime('2025-01-01'));
        $futureSubscription->method('getEndDate')->willReturn(new DateTime('2025-12-31'));

        $allSubscriptions = [$expiredSubscription, $futureSubscription];
        
        $this->mockParking->method('getSubscriptions')->willReturn($allSubscriptions);

        $result = $this->getParkingSubscriptionsUseCase->getActiveSubscriptions($this->mockParking, $now);

        $this->assertEmpty($result);
    }

    public function testGetActiveSubscriptionsWithBoundaryDates(): void
    {
        $now = new DateTime('2024-01-15 00:00:00'); // Date de référence sans heure
        
        // Test with subscription that starts on this date
        $todaySubscription = $this->createMock(Subscription::class);
        $todaySubscription->method('getStartDate')->willReturn(new DateTime('2024-01-15 00:00:00'));
        $todaySubscription->method('getEndDate')->willReturn(new DateTime('2024-02-15 23:59:59'));

        // Test with subscription that ends on this date
        $endsTodaySubscription = $this->createMock(Subscription::class);
        $endsTodaySubscription->method('getStartDate')->willReturn(new DateTime('2023-12-15 00:00:00'));
        $endsTodaySubscription->method('getEndDate')->willReturn(new DateTime('2024-01-15 23:59:59'));

        $allSubscriptions = [$todaySubscription, $endsTodaySubscription];
        
        $this->mockParking->method('getSubscriptions')->willReturn($allSubscriptions);

        $result = $this->getParkingSubscriptionsUseCase->getActiveSubscriptions($this->mockParking, $now);

        $this->assertCount(2, $result);
        $this->assertContains($todaySubscription, $result);
        $this->assertContains($endsTodaySubscription, $result);
    }
}
