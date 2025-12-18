<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class AuthenticateUserDTOTest extends TestCase
{
    public function testConstructAndProperties(): void
    {
        $dto = new AuthenticateUserDTO(' test@example.com ', 'password123');
        
        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function testFromArray(): void
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $dto = AuthenticateUserDTO::fromArray($data);

        $this->assertEquals('test@example.com', $dto->email);
        $this->assertEquals('password123', $dto->password);
    }

    public function testValidateSuccess(): void
    {
        $dto = new AuthenticateUserDTO('test@example.com', 'password123');
        $dto->validate();
        $this->assertTrue(true);
    }

    public function testValidateInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Veuillez entrer un email valide.');
        
        $dto = new AuthenticateUserDTO('invalid-email', 'password123');
        $dto->validate();
    }

    public function testValidateShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Veuillez entrer un mot de passe d\'au moins 8 caractères.');
        
        $dto = new AuthenticateUserDTO('test@example.com', 'short');
        $dto->validate();
    }
}
