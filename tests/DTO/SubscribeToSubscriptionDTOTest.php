<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class SubscribeToSubscriptionDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new SubscribeToSubscriptionDTO(' cust123 ', ' sub123 ', ' pay123 ');
        
        $this->assertEquals('cust123', $dto->customerId);
        $this->assertEquals('sub123', $dto->subscriptionTypeId);
        $this->assertEquals('pay123', $dto->paymentMethodId);
    }

    public function testFromArray(): void
    {
        $data = [
            'customerId' => 'cust123',
            'subscriptionTypeId' => 'sub123',
            'paymentMethodId' => 'pay123'
        ];

        $dto = SubscribeToSubscriptionDTO::fromArray($data);

        $this->assertEquals('cust123', $dto->customerId);
        $this->assertEquals('sub123', $dto->subscriptionTypeId);
        $this->assertEquals('pay123', $dto->paymentMethodId);
    }

    public function testValidateSuccess(): void
    {
        $dto = new SubscribeToSubscriptionDTO('cust123', 'sub123');
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateMissingCustomerId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client et type d\'abonnement requis.');
        
        $dto = new SubscribeToSubscriptionDTO('', 'sub123');
        $dto->validate();
    }

    public function testValidateMissingSubscriptionTypeId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Client et type d\'abonnement requis.');
        
        $dto = new SubscribeToSubscriptionDTO('cust123', '');
        $dto->validate();
    }
}
