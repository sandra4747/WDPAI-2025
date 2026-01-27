<?php

class UserDTO {
    public int $id;
    public string $email;
    public string $password;
    public string $name;
    public string $surname;

    public function __construct(int $id, string $email, string $password, string $name, string $surname) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
    }
}