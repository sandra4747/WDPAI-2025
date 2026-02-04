<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ .'/../dto/UserDTO.php';
require_once __DIR__ . '/../dto/UserRegistrationDTO.php';

class SecurityController extends AppController {

    private UserRepository $userRepository; 

    public function __construct(){
        $this->userRepository = new UserRepository();
    }

    public function login() {
        if (!$this->isPost()) {
            return $this->render('login');
        }
    
        $email = $_POST['email'];
        $password = $_POST['password'];
    
        $user = $this->userRepository->getUserByEmail($email);
        $invalidCredentialsMsg = ['Nieprawidłowy adres email lub hasło.'];
    
        if (!$user || !password_verify($password, $user['password'])) {
            return $this->render('login', ['messages' => $invalidCredentialsMsg]);
        }
    
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        
        // POBIERANIE ROLI DO SESJI
        $userDetails = $this->userRepository->getUserDetailsById($user['id']);
        $_SESSION['role'] = $userDetails->role; 
    
        $url = "http://$_SERVER[HTTP_HOST]";

        if ($_SESSION['role'] === 'ROLE_ADMIN') {
            // Jeśli to Admin -> Panel Administratora
            header("Location: {$url}/admin");
        } else {
            // Jeśli to Zwykły User -> Dashboard
            header("Location: {$url}/dashboard");
        }
        
        exit();
    }

    public function register() {
        if (!$this->isPost()) {
            return $this->render('register');
        }
    
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname = $_POST['lastname'] ?? '';
    
        if (empty($email) || empty($password) || empty($firstname) || empty($lastname)) {
            return $this->render("register", ["messages" => ["Wszystkie pola są wymagane!"]]);
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render("register", ["messages" => ["Niepoprawny format adresu email!"]]);
        }
    
        if (strlen($email) > 255 || strlen($firstname) > 100 || strlen($lastname) > 100) {
            return $this->render("register", ["messages" => ["Dane są zbyt długie!"]]);
        }
    
        if (strlen($password) < 8 
            || !preg_match('/[A-Z]/', $password) 
            || !preg_match('/[a-z]/', $password) 
            || !preg_match('/[0-9]/', $password)) {
            return $this->render("register", ["messages" => ["Hasło musi mieć min. 8 znaków, zawierać dużą i małą literę oraz cyfrę."]]);
        }
    
        if ($password !== $password2) {
            return $this->render("register", ["messages" => ["Hasła nie są identyczne!"]]);
        }
    
        if ($this->userRepository->getUserByEmail($email)) {
            return $this->render("register", ["messages" => ["Użytkownik o tym adresie e-mail już istnieje!"]]);
        }
    
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        $userDto = new UserRegistrationDTO(
            $email,
            $hashedPassword,
            $firstname,
            $lastname
        );
    
        try {
            $this->userRepository->createUser($userDto);
        } catch (Exception $e) {
            return $this->render("register", ["messages" => ["Wystąpił błąd podczas tworzenia konta. Spróbuj później."]]);
        }
    
        return $this->render("login", ["messages" => ["Rejestracja zakończona sukcesem. Możesz się zalogować."]]);
    }

    public function logout() {
        session_unset(); // Usuwamy wszystkie zmienne sesyjne
        session_destroy(); // Niszczymy sesję
    
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/login");
        exit();
    }
}