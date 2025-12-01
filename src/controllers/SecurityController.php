<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController {

    private UserRepository $userRepository; 

    public function __construct(){
        $this->userRepository = new UserRepository();
    }

    public function login() {
        if(!$this->isPost()){ 
            return $this->render("login");
        }
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

             // return $this->render("dashboard", ['cards' => []]);
             $user = $this->userRepository->getUsers($email); 

 
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
            return $this->render("register", ["messages"=>"Password should be the same!"]);
        }
        if ($password != $password2) {
            return $this->render("register", ["messages"=>"Password should be the same!"]);
        }
        if ($this->userRepository->getUserByEmail($email)){
            return $this->render("register", ["messages"=>"User with this email already exists!"]);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $this->userRepository->createUser(
            $email,
            $hashedPassword,
            $firstname,
            $lastname
        );
        return $this->render("login", ["messages"=>"User registered sucessfully. Please login."]);
    }

}