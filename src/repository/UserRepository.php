<?php

require_once 'Repository.php';

class UserRepository extends Repository
{
    public function createUser(
        string $email,
        string $hashedPassword,
        string $firstname
    ): void {
        $db = $this->database->connect();

        try {
            // Rozpoczęcie transakcji (Wymóg: transakcje na odpowiednim poziomie izolacji)
            $db->beginTransaction();

            // 1. Wstawienie do tabeli users (dodajemy domyślną rolę ROLE_USER = 1)
            $stmt = $db->prepare('
                INSERT INTO users (role_id, email, password)
                VALUES (?, ?, ?) RETURNING id
            ');
            
            // Zakładamy, że ROLE_USER ma ID = 1 (zgodnie z naszym init.sql)
            $stmt->execute([1, $email, $hashedPassword]);
            
            // Pobieramy ID nowo stworzonego użytkownika
            $userId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            // 2. Wstawienie do tabeli profiles (Relacja 1:1)
            $stmt = $db->prepare('
                INSERT INTO profiles (user_id, first_name)
                VALUES (?, ?)
            ');
            $stmt->execute([$userId, $firstname]);

            // Zatwierdzenie zmian
            $db->commit();

        } catch (PDOException $e) {
            // W razie błędu wycofujemy zmiany
            $db->rollBack();
            throw $e;
        }
    }

    public function getUserByEmail(string $email): ?UserDTO {
        $stmt = $this->database->connect()->prepare('
            SELECT 
                u.id, 
                u.email, 
                u.password, 
                p.first_name, 
                p.last_name 
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            WHERE u.email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            return null;
        }
    
        return new UserDTO(
            $user['id'],
            $user['email'],
            $user['password'],
            $user['first_name'] ?? '', 
            $user['last_name'] ?? ''
        );
    }
}