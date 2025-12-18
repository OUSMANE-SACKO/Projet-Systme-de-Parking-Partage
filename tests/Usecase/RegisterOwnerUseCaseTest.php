<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class RegisterOwnerUseCaseTest extends TestCase
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
            'basic_valid' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'jane.smith@example.com', 'password' => 'validpassword123'],
            'exactly_8_chars' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'jane.smith2@example.com', 'password' => 'exactly8']
        ];

        foreach ($successCases as $caseName => $case) {
            $mockUserRepository = $this->createMock(IUserRepository::class);
            $useCase = new RegisterOwnerUseCase($mockUserRepository);
            
            $mockUserRepository->expects($this->once())
                ->method('existsByEmail')
                ->with($case['email'])
                ->willReturn(false);

            $mockUserRepository->expects($this->once())
                ->method('save')
                ->with($this->isInstanceOf(Owner::class));

            $result = $useCase->execute($case['name'], $case['forename'], $case['email'], $case['password']);
            
            $this->assertInstanceOf(Owner::class, $result, "Failed for case: {$caseName}");
        }
    }

    public function testExecuteValidationErrors(): void
    {
        $errorCases = [
            'invalid_email' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'invalid-email', 'password' => 'validpassword123', 'expected_message' => 'Invalid email format.', 'check_exists' => false],
            'existing_email' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'existing@example.com', 'password' => 'validpassword123', 'expected_message' => 'Email already registered.', 'check_exists' => true, 'email_exists' => true],
            'short_password' => ['name' => 'Smith', 'forename' => 'Jane', 'email' => 'jane.smith@example.com', 'password' => 'short', 'expected_message' => 'Password must be at least 8 characters long.', 'check_exists' => true, 'email_exists' => false]
        ];

        foreach ($errorCases as $caseName => $case) {
            $mockUserRepository = $this->createMock(IUserRepository::class);
            $useCase = new RegisterOwnerUseCase($mockUserRepository);
            
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
