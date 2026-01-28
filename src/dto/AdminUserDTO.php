<?php

class AdminUserDTO {
    public int $id;
    public string $email;
    public string $role;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $avatarUrl;
    public int $totalGoals;

    public function __construct(array $data) {
        $this->id = (int)$data['id'];
        $this->email = $data['email'];
        $this->role = $data['role'];
        $this->firstName = $data['first_name'] ?? null;
        $this->lastName = $data['last_name'] ?? null;
        $this->avatarUrl = $data['avatar_url'] ?? null;
        $this->totalGoals = (int)$data['total_goals'];
    }
}