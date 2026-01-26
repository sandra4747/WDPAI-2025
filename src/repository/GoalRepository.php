<?php

require_once 'Repository.php';

class GoalRepository extends Repository {

    public function getGoalByTitle(string $searchString)
    {
        $searchString = '%' . strtolower($searchString) . '%';

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM v_goals_details
            WHERE LOWER(title) LIKE :search
        ');
        $stmt->bindParam(':search', $searchString, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getGoalsByUserId(int $userId): array 
    {
        $stmt = $this->database->connect()->prepare('
        SELECT g.*, 
               c.name as category_name, 
               c.icon as category_icon,
               calculate_progress(g.current_amount, g.target_amount) as progress_percentage
        FROM goals g
        LEFT JOIN categories c ON g.category_id = c.id
        WHERE g.user_id = :user_id
        ORDER BY g.id ASC
    ');

    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getTotalProgress(int $userId): int {
        $stmt = $this->database->connect()->prepare('SELECT calculate_total_user_progress(:userId) as total');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function addGoal(array $data, ?string $imagePath, int $userId): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO goals (user_id, category_id, title, target_amount, current_amount, target_date, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');

        // Obsługa pustej daty
        $targetDate = empty($data['target_date']) ? null : $data['target_date'];

        $stmt->execute([
            $userId,               
            (int)$data['category_id'],
            $data['title'],
            $data['target_amount'],
            0,                     
            $targetDate,
            $imagePath
        ]);
    }

    public function getCategories(): array
    {
         $stmt = $this->database->connect()->prepare('
            SELECT * FROM categories ORDER BY id ASC
        ');
        $stmt->execute();
        
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function depositFunds(int $goalId, float $amount): array
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE goals 
            SET current_amount = current_amount + :amount 
            WHERE id = :id
            RETURNING 
                calculate_progress(current_amount, target_amount) as new_progress
        ');
        
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':id', $goalId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGoalById(int $id)
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM goals WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateGoal(int $id, array $data)
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE goals 
            SET title = :title, 
                target_amount = :target_amount, 
                category_id = :category_id
            WHERE id = :id
        ');

        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':target_amount', $data['target_amount']);
        $stmt->bindParam(':category_id', $data['category_id']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }
}