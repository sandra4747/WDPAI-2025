<?php

class GoalDTO
{
    public ?int $id; 
    public string $title;
    public float $targetAmount;
    public float $currentAmount; 
    public int $categoryId;
    public ?string $categoryName; 
    public ?string $categoryIcon; 
    public ?int $userId;
    public ?string $targetDate;
    public ?string $imagePath;
    public int $progressPercentage; 

    public function __construct(array $data)
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->title = $data['title'] ?? '';
        $this->targetAmount = (float) ($data['target_amount'] ?? $data['amount'] ?? 0);
        $this->currentAmount = (float) ($data['current_amount'] ?? 0);
        
        $this->categoryId = (int) ($data['category_id'] ?? $data['category'] ?? 0);
        $this->categoryName = $data['category_name'] ?? null;
        $this->categoryIcon = $data['category_icon'] ?? null;
        
        $this->userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
        $this->targetDate = !empty($data['target_date']) ? $data['target_date'] : null;
        $this->imagePath = $data['image_path'] ?? null;
        
        $this->progressPercentage = (int) ($data['progress_percentage'] ?? 0);
    }
}