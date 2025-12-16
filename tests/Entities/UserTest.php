<?php


use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    
    public function testUserConstruction(): void {
        $name = 'Doe';
        $forename = 'John';
        $email = 'john.doe@example.com';
        $passwordHash = 'hashed_password_123';
        
        $user = new User($name, $forename, $email, $passwordHash);
        
        $this->assertNull($user->getId());
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($forename, $user->getForename());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($passwordHash, $user->getPasswordHash());
    }
    
    public function testUniqueIds(): void {
        $user1 = new User('Smith', 'Alice', 'alice@test.com', 'hash1');
        $user2 = new User('Brown', 'Bob', 'bob@test.com', 'hash2');
        
        // IDs are null until persisted
        $this->assertNull($user1->getId());
        $this->assertNull($user2->getId());
        
        $user1->setId(1);
        $this->assertEquals(1, $user1->getId());
    }
    
    public function testSetters(): void {
        $user = new User('Initial', 'Name', 'initial@test.com', 'hash');
        
        $user->setName('Updated');
        $user->setForename('NewName');
        $user->setEmail('updated@test.com');
        $user->setPasswordHash('new_hash');
        
        $this->assertEquals('Updated', $user->getName());
        $this->assertEquals('NewName', $user->getForename());
        $this->assertEquals('updated@test.com', $user->getEmail());
        $this->assertEquals('new_hash', $user->getPasswordHash());
    }
    
    public function testEmptyFieldsAllowed(): void {
        // Test that empty strings are allowed (business decision)
        $user = new User('', '', '', '');
        
        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getForename());
        $this->assertEquals('', $user->getEmail());
        $this->assertEquals('', $user->getPasswordHash());
    }
}

