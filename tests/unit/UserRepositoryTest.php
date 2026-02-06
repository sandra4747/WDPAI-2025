<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/repository/UserRepository.php';

class UserRepositoryTest extends TestCase
{
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new UserRepository();
    }

    // Przykładowe testy dla metody getUserByEmail

    public function testGetExistingUser()
    {
        $emailWBazie = 'jan@poczta.pl'; 
        
        $user = $this->repository->getUserByEmail($emailWBazie);

        $this->assertNotNull($user, "Użytkownik powinien zostać znaleziony");
        $this->assertEquals($emailWBazie, $user['email']);
    }

    public function testGetNonExistingUser()
    {
        $user = $this->repository->getUserByEmail('duch@nieistnieje.pl');

        $this->assertNull($user, "Metoda powinna zwrócić null dla nieistniejącego usera");
    }
}