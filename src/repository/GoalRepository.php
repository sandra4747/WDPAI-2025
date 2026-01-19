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
}