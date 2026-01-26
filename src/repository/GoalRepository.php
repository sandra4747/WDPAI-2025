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
            SELECT * FROM v_goals_details 
            WHERE user_id = :userId
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
}