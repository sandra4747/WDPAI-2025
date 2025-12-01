<?php

require_once 'Repository.php';

class UserRepository extends Repository
{

    public function getUsers(): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM users
        ');
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

       return $users;
    }

    public function createUser(
        string $email,
        string $hashedPassword,
        string $firstname,
        string $lastname,
        string $bio = ''
    ): void {
    
        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (firstname, lastname, email, password, bio)
            VALUES (?, ?, ?, ?, ?);
            "
        );
    
        $query->execute([
            $firstname,
            $lastname,
            $email,
            $hashedPassword,
            $bio
        ]);
    }
    

    public function getUserByEmail(string $email) {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users WHERE email = :email
            "
        );
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM users WHERE email = :email
        ');
        $query->bindParam(':email', $email);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);

       return $user;
    }

}