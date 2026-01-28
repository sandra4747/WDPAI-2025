<?php

class UserDTO {
    public int $id;
    public string $email;
    public string $name;
    public string $surname;
    public string $role;
    public ?string $avatarUrl;

    public function __construct(int $id, string $email, string $name, string $surname, string $role, ?string $avatarUrl = null) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
        $this->role = $role;
        $this->avatarUrl = $avatarUrl;
    }
}