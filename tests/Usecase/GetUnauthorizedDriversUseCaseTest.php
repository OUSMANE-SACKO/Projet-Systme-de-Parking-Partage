<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class GetUnauthorizedDriversUseCaseTest extends TestCase
{
    private GetUnauthorizedDriversUseCase $getUnauthorizedDriversUseCase;
    private MockObject $mockParking;
    private MockObject $mockCustomer;
    private DateTime $checkTime;

    protected function setUp(): void
    {
        $this->getUnauthorizedDriversUseCase = new GetUnauthorizedDriversUseCase();
        $this->mockParking = $this->createMock(Parking::class);
        $this->mockCustomer = $this->createMock(Customer::class);
        $this->checkTime = new DateTime('2024-01-15 14:00:00');
    }

    public function testExecuteWithNoParkingSpaces(): void
    {
        $this->mockParking->method('getParkingSpaces')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['unauthorizedDrivers']);
        $this->assertEquals('2024-01-15 14:00:00', $result['checkTime']);
    }

    public function testExecuteWithEmptyParkingSpaces(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn(null);
        
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['unauthorizedDrivers']);
    }

    public function testExecuteWithAuthorizedCustomerWithValidReservation(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null); // Still parked
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-01-15 15:00:00'));
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['unauthorizedDrivers']);
    }

    public function testExecuteWithAuthorizedCustomerWithValidSubscription(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getWeeklyTimeSlots')->willReturn([]); // Full access
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-01-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-01-31'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['unauthorizedDrivers']);
    }

    public function testExecuteWithUnauthorizedCustomer(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['unauthorizedDrivers']);
        
        $unauthorizedDriver = $result['unauthorizedDrivers'][0];
        $this->assertSame($this->mockCustomer, $unauthorizedDriver['customer']);
        $this->assertSame($mockSpace, $unauthorizedDriver['parkingSpace']);
        $this->assertEquals('2024-01-15 13:00:00', $unauthorizedDriver['parkedSince']);
        $this->assertEquals('2024-01-15 14:00:00', $unauthorizedDriver['checkTime']);
    }

    public function testExecuteWithCustomerLeftParking(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(new DateTime('2024-01-15 13:30:00')); // Already left
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(0, $result['count']);
        $this->assertEmpty($result['unauthorizedDrivers']);
    }

    public function testExecuteWithExpiredReservation(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 10:00:00'));
        
        $mockReservation = $this->createMock(Reservation::class);
        $mockReservation->method('getCustomer')->willReturn($this->mockCustomer);
        $mockReservation->method('getStartTime')->willReturn(new DateTime('2024-01-15 10:00:00'));
        $mockReservation->method('getEndTime')->willReturn(new DateTime('2024-01-15 12:00:00')); // Expired
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([$mockReservation]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['unauthorizedDrivers']);
    }

    public function testExecuteWithExpiredSubscription(): void
    {
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getWeeklyTimeSlots')->willReturn([]);
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2023-12-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2023-12-31')); // Expired
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(1, $result['count']);
        $this->assertCount(1, $result['unauthorizedDrivers']);
    }

    public function testExecuteWithSubscriptionWithTimeSlots(): void
    {
        $checkTime = new DateTime('2024-01-15 14:00:00'); // Monday
        
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getWeeklyTimeSlots')->willReturn([
            ['day' => 'Monday', 'startTime' => '09:00', 'endTime' => '17:00']
        ]);
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-01-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-01-31'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $checkTime);

        $this->assertEquals(0, $result['count']); // Should be authorized (14:00 is within 09:00-17:00)
        $this->assertEmpty($result['unauthorizedDrivers']);
    }

    public function testExecuteWithSubscriptionOutsideTimeSlot(): void
    {
        $checkTime = new DateTime('2024-01-15 20:00:00'); // Monday evening
        
        $mockSpace = $this->createMock(ParkingSpace::class);
        $mockSpace->method('getCustomer')->willReturn($this->mockCustomer);
        $mockSpace->method('getEndTime')->willReturn(null);
        $mockSpace->method('getStartTime')->willReturn(new DateTime('2024-01-15 19:00:00'));
        
        $mockSubscriptionType = $this->createMock(SubscriptionType::class);
        $mockSubscriptionType->method('getWeeklyTimeSlots')->willReturn([
            ['day' => 'Monday', 'startTime' => '09:00', 'endTime' => '17:00']
        ]);
        
        $mockSubscription = $this->createMock(Subscription::class);
        $mockSubscription->method('getStartDate')->willReturn(new DateTime('2024-01-01'));
        $mockSubscription->method('getEndDate')->willReturn(new DateTime('2024-01-31'));
        $mockSubscription->method('getSubscriptionType')->willReturn($mockSubscriptionType);
        
        $this->mockCustomer->method('getSubscriptions')->willReturn([$mockSubscription]);
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $checkTime);

        $this->assertEquals(1, $result['count']); // Should be unauthorized (20:00 is outside 09:00-17:00)
        $this->assertCount(1, $result['unauthorizedDrivers']);
    }

    public function testExecuteWithMultipleCustomers(): void
    {
        $mockCustomer1 = $this->createMock(Customer::class);
        $mockCustomer2 = $this->createMock(Customer::class);
        
        $mockSpace1 = $this->createMock(ParkingSpace::class);
        $mockSpace1->method('getCustomer')->willReturn($mockCustomer1);
        $mockSpace1->method('getEndTime')->willReturn(null);
        $mockSpace1->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:00:00'));
        
        $mockSpace2 = $this->createMock(ParkingSpace::class);
        $mockSpace2->method('getCustomer')->willReturn($mockCustomer2);
        $mockSpace2->method('getEndTime')->willReturn(null);
        $mockSpace2->method('getStartTime')->willReturn(new DateTime('2024-01-15 13:30:00'));
        
        $mockCustomer1->method('getSubscriptions')->willReturn([]);
        $mockCustomer2->method('getSubscriptions')->willReturn([]);
        
        $this->mockParking->method('getParkingSpaces')->willReturn([$mockSpace1, $mockSpace2]);
        $this->mockParking->method('getReservations')->willReturn([]);

        $result = $this->getUnauthorizedDriversUseCase->execute($this->mockParking, $this->checkTime);

        $this->assertEquals(2, $result['count']);
        $this->assertCount(2, $result['unauthorizedDrivers']);
    }
}