<?php

require_once 'Repository.php';

class CardsRepository extends Repository {

    public function getCardsByTitle(string $searchString)
    {
        $searchString = '%' . strtolower($searchString) . '%';

        $stmt = $this->database->connect()->prepare('
            SELECT * FROM cards
            WHERE LOWER(title) LIKE :search OR LOWER(description) LIKE :search
        ');
        $stmt->bindParam(':search', $searchString, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCardsByUserId(int $userId): array 
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM cards 
            WHERE id = :userId
        ');
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Zwraca tablicę kart lub pustą tablicę, jeśli nic nie znaleziono
        return $cards ?: [];
    }
}