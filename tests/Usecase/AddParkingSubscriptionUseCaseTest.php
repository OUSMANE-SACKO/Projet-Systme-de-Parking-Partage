<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../vendor/autoload.php';

class AddParkingSubscriptionUseCaseTest extends TestCase
{
    private AddParkingSubscriptionUseCase $addParkingSubscriptionUseCase;
    private MockObject $mockParking;

    protected function setUp(): void
    {
        $this->addParkingSubscriptionUseCase = new AddParkingSubscriptionUseCase();
        $this->mockParking = $this->createMock(Parking::class);
    }

    public function testExecuteWithValidParameters(): void
    {
        $name = 'Basic Plan';
        $description = 'Basic parking subscription';
        $monthlyPrice = 50.0;
        $weeklyTimeSlots = ['Monday 9-17', 'Friday 9-17'];

        $this->mockParking->expects($this->once())
            ->method('addSubscriptionType')
            ->with($this->isInstanceOf(SubscriptionType::class));

        // Note: We expect this will return a SubscriptionType, not Subscription
        $result = $this->addParkingSubscriptionUseCase->execute($this->mockParking, $name, $description, $monthlyPrice, $weeklyTimeSlots);

        $this->assertInstanceOf(SubscriptionType::class, $result);
    }

    public function testExecuteWithEmptyTimeSlots(): void
    {
        $name = 'Unlimited Plan';
        $description = 'Unlimited access parking subscription';
        $monthlyPrice = 150.0;
        $weeklyTimeSlots = []; // Empty means full access

        $this->mockParking->expects($this->once())
            ->method('addSubscriptionType')
            ->with($this->isInstanceOf(SubscriptionType::class));

        $result = $this->addParkingSubscriptionUseCase->execute($this->mockParking, $name, $description, $monthlyPrice, $weeklyTimeSlots);

        $this->assertInstanceOf(SubscriptionType::class, $result);
    }

    public function testExecuteCallsCorrectParkingMethod(): void
    {
        $name = 'Test Plan';
        $description = 'Test description';
        $monthlyPrice = 50.0;
        $weeklyTimeSlots = [];

        // Verify that addSubscriptionType is called with a SubscriptionType instance
        $this->mockParking->expects($this->once())
            ->method('addSubscriptionType')
            ->with($this->callback(function($subscription) {
                return $subscription instanceof SubscriptionType;
            }));

        $result = $this->addParkingSubscriptionUseCase->execute($this->mockParking, $name, $description, $monthlyPrice, $weeklyTimeSlots);

        $this->assertInstanceOf(SubscriptionType::class, $result);
    }
}