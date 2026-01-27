<?php

require_once 'Repository.php';
// Import DTO (opcjonalny jeśli autoloader działa, ale dobra praktyka)
require_once __DIR__ .'/../dto/GoalDTO.php';

class GoalRepository extends Repository {

    // ... metody getGoalByTitle, getGoalsByUserId, getTotalProgress BEZ ZMIAN ...

    public function getGoalByTitle(string $searchString)
    {
        $searchString = '%' . strtolower($searchString) . '%';
        $stmt = $this->database->connect()->prepare('SELECT * FROM v_goals_details WHERE LOWER(title) LIKE :search');
        $stmt->bindParam(':search', $searchString, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalsByUserId(int $userId): array 
    {
        $stmt = $this->database->connect()->prepare('
        SELECT g.*, g.image_path, c.name as category_name, c.icon as category_icon,
               calculate_progress(g.current_amount, g.target_amount) as progress_percentage
        FROM goals g
        LEFT JOIN categories c ON g.category_id = c.id
        WHERE g.user_id = :user_id
        ORDER BY g.id ASC');
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        // Pobieramy surowe dane
        $rawGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Przepakowujemy je w obiekty DTO
        $goalObjects = [];
        foreach ($rawGoals as $rawGoal) {
            // Dodajemy brakujące klucze, których oczekuje konstruktor DTO
            // (bo np. w bazie kolumna nazywa się 'target_amount', a w formularzu mogła być 'amount')
            $rawGoal['amount'] = $rawGoal['target_amount'];
            
            // Tworzymy obiekt
            $goalObjects[] = new GoalDTO($rawGoal);
        }

        return $goalObjects;
    }

    public function getTotalProgress(int $userId): int {
        $stmt = $this->database->connect()->prepare('SELECT calculate_total_user_progress(:userId) as total');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // --- ZMIANA: Przyjmujemy GoalDTO zamiast array i luźnych zmiennych ---
    public function addGoal(GoalDTO $goal): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO goals (user_id, category_id, title, target_amount, current_amount, target_date, image_path)
            VALUES (:user_id, :category_id, :title, :target_amount, 0, :target_date, :image_path)
        ');

        // Używamy danych z obiektu DTO
        $stmt->bindParam(':user_id', $goal->userId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $goal->categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $goal->title, PDO::PARAM_STR);
        $stmt->bindParam(':target_amount', $goal->targetAmount, PDO::PARAM_STR);
        $stmt->bindParam(':target_date', $goal->targetDate, PDO::PARAM_STR); // Może być null, PDO to obsłuży
        $stmt->bindParam(':image_path', $goal->imagePath, PDO::PARAM_STR);

        $stmt->execute();
    }

    // ... metoda getCategories BEZ ZMIAN ...
    public function getCategories(): array
    {
         $stmt = $this->database->connect()->prepare('SELECT * FROM categories ORDER BY id ASC');
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ... metoda depositFunds BEZ ZMIAN ...
    public function depositFunds(int $goalId, float $amount): array
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE goals SET current_amount = current_amount + :amount WHERE id = :id
            RETURNING calculate_progress(current_amount, target_amount) as new_progress
        ');
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':id', $goalId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ... metoda getGoalById BEZ ZMIAN ...
    public function getGoalById(int $id)
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM goals WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- ZMIANA: Przyjmujemy GoalDTO ---
    public function updateGoal(int $id, GoalDTO $goal)
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE goals 
            SET title = :title, 
                target_amount = :target_amount, 
                category_id = :category_id
            WHERE id = :id
        ');

        $stmt->bindParam(':title', $goal->title);
        $stmt->bindParam(':target_amount', $goal->targetAmount);
        $stmt->bindParam(':category_id', $goal->categoryId);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    // ... reszta metod (deleteGoal, getGoalLogs) BEZ ZMIAN ...
    public function deleteGoal(int $goalId, int $userId)
    {
        $stmt = $this->database->connect()->prepare('DELETE FROM goals WHERE id = :id AND user_id = :uid');
        $stmt->bindParam(':id', $goalId, PDO::PARAM_INT);
        $stmt->bindParam(':uid', $userId, PDO::PARAM_INT); 
        $stmt->execute();
    }

    public function getGoalLogs(int $goalId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT change_date, new_amount, old_amount FROM goal_logs WHERE goal_id = :id ORDER BY change_date DESC
        ');
        $stmt->bindParam(':id', $goalId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}