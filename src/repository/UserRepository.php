<?php

require_once 'Repository.php';

class UserRepository extends Repository
{
    public function createUser(UserRegistrationDTO $userDto): void {
        $db = $this->database->connect();
    
        try {
            $db->beginTransaction();
    
            $stmt = $db->prepare('
                INSERT INTO users (role_id, email, password)
                VALUES (?, ?, ?) RETURNING id
            ');
            
            $stmt->execute([
                1, 
                $userDto->email,
                $userDto->password
            ]);
            
            $userId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
            $stmt = $db->prepare('
                INSERT INTO profiles (user_id, first_name, last_name)
                VALUES (?, ?, ?)
            ');
            
            $stmt->execute([
                $userId, 
                $userDto->firstName, 
                $userDto->lastName
            ]);
    
            $db->commit();
    
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
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
    
        return $user; 
    }

    public function getUserDetailsById(int $id): ?UserDTO {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM v_user_details WHERE id = :id
        ');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$data) return null;
    
        return new UserDTO(
            (int)$data['id'],
            $data['email'],
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['role'],      
            $data['avatar_url'] ?? null
        );
    }

    public function updateUserProfile(int $id, string $name, string $surname, string $email, ?string $avatarUrl): void 
    {
        $db = $this->database->connect();
        
        try {
            $db->beginTransaction();

            $stmt1 = $db->prepare('UPDATE users SET email = :email WHERE id = :id');
            $stmt1->execute([':email' => $email, ':id' => $id]);

            $stmt2 = $db->prepare('
                UPDATE profiles SET first_name = :name, last_name = :surname, avatar_url = :avatar 
                WHERE user_id = :id
            ');
            $stmt2->execute([
                ':name' => $name,
                ':surname' => $surname,
                ':avatar' => $avatarUrl,
                ':id' => $id
            ]);

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public function getUsersDetails(): array {
        $stmt = $this->database->connect()->prepare('SELECT * FROM v_user_details');
        $stmt->execute();
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $users = [];
        foreach ($usersData as $data) {
            $users[] = new AdminUserDTO($data);
        }
        return $users;
    }
}