<?php

require_once 'Repository.php';
require_once __DIR__ .'/../dto/GoalDTO.php';

class GoalRepository extends Repository {


    public function getGoalByTitle(string $searchString): array
    {
        $searchString = '%' . strtolower($searchString) . '%';
        $stmt = $this->database->connect()->prepare('SELECT * FROM v_goals_details WHERE LOWER(title) LIKE :search');
        $stmt->bindParam(':search', $searchString, PDO::PARAM_STR);
        $stmt->execute();
        
        $rawGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $goalObjects = [];
        
        foreach ($rawGoals as $rawGoal) {
            // Dopasowujemy klucze pod konstruktor DTO (jeśli trzeba)
            $rawGoal['amount'] = $rawGoal['target_amount'];
            $goalObjects[] = new GoalDTO($rawGoal);
        }
        
        return $goalObjects;
    }

    public function getGoalsByUserId(int $userId): array 
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM v_goals_details 
            WHERE user_id = :user_id 
            ORDER BY id ASC
        ');
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    
        $rawGoals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $goalObjects = [];
        foreach ($rawGoals as $rawGoal) {
            $rawGoal['amount'] = $rawGoal['target_amount'];
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

    public function addGoal(GoalDTO $goal): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO goals (user_id, category_id, title, target_amount, current_amount, target_date, image_path)
            VALUES (:user_id, :category_id, :title, :target_amount, 0, :target_date, :image_path)
        ');

        $stmt->bindParam(':user_id', $goal->userId, PDO::PARAM_INT);
        $stmt->bindParam(':category_id', $goal->categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $goal->title, PDO::PARAM_STR);
        $stmt->bindParam(':target_amount', $goal->targetAmount, PDO::PARAM_STR);
        $stmt->bindParam(':target_date', $goal->targetDate, PDO::PARAM_STR); 
        $stmt->bindParam(':image_path', $goal->imagePath, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function getCategories(): array
    {
         $stmt = $this->database->connect()->prepare('SELECT * FROM categories ORDER BY id ASC');
         $stmt->execute();
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function getGoalById(int $id): ?GoalDTO
    {
        $stmt = $this->database->connect()->prepare('SELECT * FROM goals WHERE id = :id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $rawGoal = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$rawGoal) {
            return null;
        }

        // Dopasowujemy klucze
        $rawGoal['amount'] = $rawGoal['target_amount'];
        return new GoalDTO($rawGoal);
    }

    public function updateGoal(int $id, GoalDTO $goal)
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE goals 
            SET title = :title, 
                target_amount = :target_amount, 
                category_id = :category_id,
                image_path = :image_path
            WHERE id = :id
        ');
    
        // Bindujemy wszystkie parametry z obiektu DTO
        $stmt->bindParam(':title', $goal->title, PDO::PARAM_STR);
        $stmt->bindParam(':target_amount', $goal->targetAmount, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $goal->categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':image_path', $goal->imagePath, PDO::PARAM_STR); // <--- TO DODAJEMY
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
        $stmt->execute();
    }
    
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