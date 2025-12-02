<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class AuthenticateUserUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('PEPPER=test_pepper_secret_key');
    }

    protected function tearDown(): void
    {
        putenv('PEPPER=');
    }

    public function testExecuteSuccessfulAuthentication(): void
    {
        $mockUserRepository = $this->createMock(IUserRepository::class);
        $useCase = new AuthenticateUserUseCase($mockUserRepository);
        
        $email = 'test@example.com';
        $password = 'validpassword';
        
        $mockUser = $this->createMock(User::class);
        $pepper = 'test_pepper_secret_key';
        $password_peppered = hash_hmac("sha256", $password, $pepper);
        $hashedPassword = password_hash($password_peppered, PASSWORD_DEFAULT);
        
        $mockUser->method('getPasswordHash')->willReturn($hashedPassword);
        
        $mockUserRepository->expects($this->once())
            ->method('findByEmail')
            ->with($email)
            ->willReturn($mockUser);

        $result = $useCase->execute($email, $password);

        $this->assertTrue($result['authenticated']);
        $this->assertSame($mockUser, $result['user']);
        $this->assertEquals('Authentication successful.', $result['message']);
    }

    public function testExecuteFailedAuthentications(): void
    {
        $failureCases = [
            'invalid_email' => ['email' => 'invalid-email', 'password' => 'validpassword', 'expected_message' => 'Invalid email format.', 'user_lookup' => false],
            'empty_email' => ['email' => '', 'password' => 'validpassword', 'expected_message' => 'Invalid email format.', 'user_lookup' => false],
            'user_not_found' => ['email' => 'notfound@example.com', 'password' => 'validpassword', 'expected_message' => 'Invalid credentials.', 'user_lookup' => true, 'user_found' => false],
            'null_user' => ['email' => 'test@example.com', 'password' => 'validpassword', 'expected_message' => 'Invalid credentials.', 'user_lookup' => true, 'user_found' => false],
            'invalid_password' => ['email' => 'test@example.com', 'password' => 'invalidpassword', 'expected_message' => 'Invalid credentials.', 'user_lookup' => true, 'user_found' => true, 'correct_password' => 'correctpassword'],
            'empty_password' => ['email' => 'test@example.com', 'password' => '', 'expected_message' => 'Invalid credentials.', 'user_lookup' => true, 'user_found' => true, 'correct_password' => 'nonemptypassword']
        ];

        foreach ($failureCases as $caseName => $case) {
            $mockUserRepository = $this->createMock(IUserRepository::class);
            $useCase = new AuthenticateUserUseCase($mockUserRepository);
            
            if ($case['user_lookup']) {
                if ($case['user_found']) {
                    $mockUser = $this->createMock(User::class);
                    $pepper = 'test_pepper_secret_key';
                    $correctPassword = $case['correct_password'];
                    $correctPasswordPeppered = hash_hmac("sha256", $correctPassword, $pepper);
                    $hashedPassword = password_hash($correctPasswordPeppered, PASSWORD_DEFAULT);
                    $mockUser->method('getPasswordHash')->willReturn($hashedPassword);
                    
                    $mockUserRepository->expects($this->once())
                        ->method('findByEmail')
                        ->with($case['email'])
                        ->willReturn($mockUser);
                } else {
                    $mockUserRepository->expects($this->once())
                        ->method('findByEmail')
                        ->with($case['email'])
                        ->willReturn(null);
                }
            } else {
                $mockUserRepository->expects($this->never())
                    ->method('findByEmail');
            }

            $result = $useCase->execute($case['email'], $case['password']);

            $this->assertFalse($result['authenticated'], "Should fail for case: {$caseName}");
            $this->assertNull($result['user'], "User should be null for case: {$caseName}");
            $this->assertEquals($case['expected_message'], $result['message'], "Wrong message for case: {$caseName}");
        }
    }
}