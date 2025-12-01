<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class HashPasswordUseCaseTest extends TestCase
{
    private HashPasswordUseCase $hashPasswordUseCase;

    protected function setUp(): void
    {
        $this->hashPasswordUseCase = new HashPasswordUseCase();
        putenv('PEPPER=test_pepper_secret_key');
    }

    protected function tearDown(): void
    {
        putenv('PEPPER=');
    }

    public function testExecuteWithValidPasswords(): void
    {
        $passwords = ['validpassword123', 'abc', '!@#$%^&*()', 'üñíçødé', str_repeat('a', 1000)];
        
        foreach ($passwords as $password) {
            $result = $this->hashPasswordUseCase->execute($password);
            
            $this->assertIsString($result);
            $this->assertNotEmpty($result);
            $this->assertNotEquals($password, $result);
            
            // Verify hash
            $pepper = 'test_pepper_secret_key';
            $password_peppered = hash_hmac("sha256", $password, $pepper);
            $this->assertTrue(password_verify($password_peppered, $result));
        }
    }

    public function testExecuteWithInvalidPasswords(): void
    {
        $invalidPasswords = ['', '   ', "\t\n  \r"];
        
        foreach ($invalidPasswords as $password) {
            try {
                $this->hashPasswordUseCase->execute($password);
                $this->fail('Expected InvalidArgumentException for password: ' . var_export($password, true));
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Password cannot be empty', $e->getMessage());
            }
        }
    }

    public function testExecuteProducesUniqueHashes(): void
    {
        $password = 'samepassword';
        $result1 = $this->hashPasswordUseCase->execute($password);
        $result2 = $this->hashPasswordUseCase->execute($password);
        
        $this->assertNotEquals($result1, $result2); // Different salts
    }
}