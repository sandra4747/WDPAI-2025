<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ .'/../dto/UserDTO.php';

class SecurityController extends AppController {

    private UserRepository $userRepository; 

    public function __construct(){
        $this->userRepository = new UserRepository();
    }

    public function login() {
        if (!$this->isPost()) {
            return $this->render("login");
        }
    
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
    
        // Repozytorium zwraca obiekt UserDTO lub null
        $user = $this->userRepository->getUserByEmail($email);
    
        if (!$user || !password_verify($password, $user->password)) {
            return $this->render("login", [
                "messages" => "Niepoprawny email lub hasło."
            ]);
        }
    
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        
        session_regenerate_id(true);
    
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/dashboard");
        exit();
    }

    public function register() {
        
        if($this->isGet()){
            return $this->render('register');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname = $_POST['lastname'] ?? '';

        if(empty($email) || empty($password) || empty($firstname) || empty($lastname)){
            return $this->render("register", ["messages"=>"Wszystkie pola są wymagane!"]);
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return $this->render("register", ["messages"=>"Niepoprawny format email!"]);
        }

        if(strlen($password) < 8 
            || !preg_match('/[A-Z]/', $password) 
            || !preg_match('/[a-z]/', $password) 
            || !preg_match('/[0-9]/', $password)) {
            return $this->render("register", ["messages"=>"Hasło musi mieć min. 8 znaków, dużą literę, małą literę i cyfrę."]);
        }

        if($password !== $password2){
            return $this->render("register", ["messages"=>"Hasła muszą być takie same!"]);
        }

        if($this->userRepository->getUserByEmail($email)){
            return $this->render("register", ["messages"=>"Użytkownik z tym emailem już istnieje!"]);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstname,
            $lastname
        );

        return $this->render("login", ["messages"=>"Rejestracja zakończona sukcesem. Zaloguj się."]);
    }
}