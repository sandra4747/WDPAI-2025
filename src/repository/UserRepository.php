<?php

require_once 'Repository.php';

class UserRepository extends Repository
{
    public function createUser(UserRegistrationDTO $userDto): void {
        // 1. Pobieramy połączenie z bazą
        $db = $this->database->connect();
    
        try {
            // 2. ROZPOCZYNAMY TRANSAKCJĘ
            $db->beginTransaction();
    
            // 3. WSTAWIANIE DO TABELI USERS
            $stmt = $db->prepare('
                INSERT INTO users (role_id, email, password)
                VALUES (?, ?, ?) RETURNING id
            ');
            
            $stmt->execute([
                1, // Domyślna rola (np. ROLE_USER)
                $userDto->email,
                $userDto->password
            ]);
            
            // Pobieramy ID, które baza przed chwilą nadała użytkownikowi
            $userId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
            // 4. WSTAWIANIE DO TABELI PROFILES (używając uzyskanego userId)
            $stmt = $db->prepare('
                INSERT INTO profiles (user_id, first_name, last_name)
                VALUES (?, ?, ?)
            ');
            
            $stmt->execute([
                $userId, 
                $userDto->firstName, 
                $userDto->lastName
            ]);
    
            // 5. ZATWIERDZAMY TRANSAKCJĘ
            // Dopiero teraz dane stają się widoczne dla innych i zostają na stałe w bazie
            $db->commit();
    
        } catch (PDOException $e) {
            // 6. COFAMY ZMIANY W RAZIE BŁĘDU
            // Jeśli cokolwiek poszło nie tak, baza wróci do stanu sprzed beginTransaction
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            // Wyrzucamy błąd dalej, żeby kontroler mógł go obsłużyć (np. pokazać komunikat)
            throw $e;
        }
    }

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, password FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
    
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$user) {
            return null;
        }
    
        return $user; // Zwracamy tablicę z id, email i password
    }

    public function getUserDetailsById(int $id): ?UserDTO 
    {
        $stmt = $this->database->connect()->prepare('
            SELECT u.id, u.email, p.first_name, p.last_name 
            FROM users u 
            LEFT JOIN profiles p ON u.id = p.user_id 
            WHERE u.id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        return new UserDTO(
            (int)$data['id'],
            $data['email'],
            $data['first_name'] ?? '',
            $data['last_name'] ?? ''
        );
    }
}