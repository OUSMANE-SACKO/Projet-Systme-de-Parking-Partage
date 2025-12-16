<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class RegisterCustomerUseCaseTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('PEPPER=test_pepper_secret_key');
    }

    protected function tearDown(): void
    {
        putenv('PEPPER=');
    }

    public function testExecuteSuccessfulRegistrations(): void
    {
        $successCases = [
            'basic_valid' => ['name' => 'Doe', 'forename' => 'John', 'email' => 'john.doe@example.com', 'password' => 'validpassword123'],
            'exactly_8_chars' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'jane.smith@example.com', 'password' => 'exactly8'],
            'empty_name' => ['name' => '', 'forename' => 'John', 'email' => 'john.anonymous@example.com', 'password' => 'validpassword123'],
            'empty_forename' => ['name' => 'Doe', 'forename' => '', 'email' => 'anonymous.doe@example.com', 'password' => 'validpassword123'],
            'long_password' => ['name' => 'Test', 'forename' => 'User', 'email' => 'test.user@example.com', 'password' => str_repeat('a', 100)],
            'special_chars' => ['name' => "O'Connor", 'forename' => "Jean-Pierre", 'email' => 'jean-pierre.oconnor@example.com', 'password' => 'validpassword123']
        ];

        foreach ($successCases as $caseName => $case) {
            $mockUserRepository = $this->createMock(IUserRepository::class);
            $useCase = new RegisterCustomerUseCase($mockUserRepository);
            
            $mockUserRepository->expects($this->once())
                ->method('existsByEmail')
                ->with($case['email'])
                ->willReturn(false);

            $mockUserRepository->expects($this->once())
                ->method('save')
                ->with($this->isInstanceOf(Customer::class));

            $result = $useCase->execute($case['name'], $case['forename'], $case['email'], $case['password']);
            
            $this->assertInstanceOf(Customer::class, $result, "Failed for case: {$caseName}");
        }
    }

    public function testExecuteValidationErrors(): void
    {
        $errorCases = [
            'invalid_email' => ['name' => 'Doe', 'forename' => 'John', 'email' => 'invalid-email', 'password' => 'validpassword123', 'expected_message' => 'Invalid email format.', 'check_exists' => false],
            'existing_email' => ['name' => 'Doe', 'forename' => 'John', 'email' => 'existing@example.com', 'password' => 'validpassword123', 'expected_message' => 'Email already registered.', 'check_exists' => true, 'email_exists' => true],
            'short_password' => ['name' => 'Doe', 'forename' => 'John', 'email' => 'john.doe@example.com', 'password' => 'short', 'expected_message' => 'Password must be at least 8 characters long.', 'check_exists' => true, 'email_exists' => false]
        ];

        foreach ($errorCases as $caseName => $case) {
            $mockUserRepository = $this->createMock(IUserRepository::class);
            $useCase = new RegisterCustomerUseCase($mockUserRepository);
            
            if ($case['check_exists']) {
                $mockUserRepository->expects($this->once())
                    ->method('existsByEmail')
                    ->with($case['email'])
                    ->willReturn($case['email_exists'] ?? false);
            } else {
                $mockUserRepository->expects($this->never())
                    ->method('existsByEmail');
            }

            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage($case['expected_message']);

            $useCase->execute($case['name'], $case['forename'], $case['email'], $case['password']);
        }
    }
}
