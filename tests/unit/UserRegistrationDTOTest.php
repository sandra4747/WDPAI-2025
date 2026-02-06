<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/dto/UserRegistrationDTO.php';

class UserRegistrationDTOTest extends TestCase
{
    public function testDtoAssignsPropertiesCorrectly()
    {
        $email = 'jan.kowalski@example.com';
        $password = 'TajneHaslo123!';
        $firstName = 'Jan';
        $lastName = 'Kowalski';

        $dto = new UserRegistrationDTO($email, $password, $firstName, $lastName);

        $this->assertEquals($email, $dto->email);
        $this->assertEquals($password, $dto->password);
        $this->assertEquals($firstName, $dto->firstName);
        $this->assertEquals($lastName, $dto->lastName);
    }
}