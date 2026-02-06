<?php

require_once 'Repository.php';

class BadgeRepository extends Repository
{
    public function getUserBadges(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT b.name, b.icon, b.description
            FROM badges b
            JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = :id
            ORDER BY ub.awarded_at DESC
        ');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBadge(int $userId, int $badgeId)
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO user_badges (user_id, badge_id)
            VALUES (:user_id, :badge_id)
            ON CONFLICT (user_id, badge_id) DO NOTHING
        ');

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':badge_id', $badgeId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function checkAchievements(int $userId)
    {
        $conn = $this->database->connect();

        $stmt = $conn->prepare('SELECT COUNT(*) FROM goals WHERE user_id = :id');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $goalsCount = $stmt->fetchColumn();

        if ($goalsCount >= 1) {
            $this->addBadge($userId, 1); 
        }

        $stmt = $conn->prepare('SELECT SUM(current_amount) FROM goals WHERE user_id = :id');
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $totalAmount = $stmt->fetchColumn();

        if ($totalAmount > 10000) {
            $this->addBadge($userId, 2); 
        }
    }
}