<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../../vendor/autoload.php';

class CustomerControllerTest extends TestCase
{
    private MockObject $registerCustomerUseCase;
    private MockObject $authenticateUserUseCase;
    private CustomerController $controller;

    protected function setUp(): void
    {
        $this->registerCustomerUseCase = $this->createMock(RegisterCustomerUseCase::class);
        $this->authenticateUserUseCase = $this->createMock(AuthenticateUserUseCase::class);
        
        $this->controller = new CustomerController(
            $this->registerCustomerUseCase,
            $this->authenticateUserUseCase
        );
    }

    public function testRegisterSuccess(): void
    {
        $requestData = [
            'name' => 'Doe',
            'forename' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        $expectedUser = $this->createMock(Customer::class);
        
        $this->registerCustomerUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('Doe', 'John', 'john.doe@example.com', 'password123')
            ->willReturn($expectedUser);

        $result = $this->controller->register($requestData);
        
        $this->assertSame($expectedUser, $result);
    }

    public function testRegisterValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        $requestData = [
            'name' => '', // Invalid
            'forename' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        $this->controller->register($requestData);
    }

    public function testAuthenticateSuccess(): void
    {
        $requestData = [
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        $expectedResult = ['token' => 'abc'];
        
        $this->authenticateUserUseCase
            ->expects($this->once())
            ->method('execute')
            ->with('john.doe@example.com', 'password123')
            ->willReturn($expectedResult);

        $result = $this->controller->authenticate($requestData);
        
        $this->assertSame($expectedResult, $result);
    }

    public function testAuthenticateValidationFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        $requestData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $this->controller->authenticate($requestData);
    }
}
