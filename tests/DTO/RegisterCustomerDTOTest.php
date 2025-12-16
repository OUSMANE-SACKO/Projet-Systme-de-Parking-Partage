<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class RegisterCustomerDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new RegisterCustomerDTO(' Doe ', ' John ', ' john.doe@example.com ', 'password123');
        
        $this->assertEquals('Doe', $dto->name);
        $this->assertEquals('John', $dto->forename);
        $this->assertEquals('john.doe@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function testFromArray(): void
    {
        $data = [
            'name' => 'Doe',
            'forename' => 'John',
            'email' => 'john.doe@example.com',
            'password' => 'password123'
        ];

        $dto = RegisterCustomerDTO::fromArray($data);

        $this->assertEquals('Doe', $dto->name);
        $this->assertEquals('John', $dto->forename);
        $this->assertEquals('john.doe@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function testValidateSuccess(): void
    {
        $dto = new RegisterCustomerDTO('Doe', 'John', 'john.doe@example.com', 'password123');
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateMissingName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name is required.');
        
        $dto = new RegisterCustomerDTO('', 'John', 'john.doe@example.com', 'password123');
        $dto->validate();
    }

    public function testValidateMissingForename(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Forename is required.');
        
        $dto = new RegisterCustomerDTO('Doe', '', 'john.doe@example.com', 'password123');
        $dto->validate();
    }

    public function testValidateInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A valid email is required.');
        
        $dto = new RegisterCustomerDTO('Doe', 'John', 'invalid-email', 'password123');
        $dto->validate();
    }

    public function testValidateShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long.');
        
        $dto = new RegisterCustomerDTO('Doe', 'John', 'john.doe@example.com', 'short');
        $dto->validate();
    }
}
