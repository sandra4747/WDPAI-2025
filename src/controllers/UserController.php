<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../dto/UserDTO.php';

class UserController extends AppController {

    private $userRepository;

    public function __construct() {
        $this->checkLogin(); // Ochrona sesją
        parent::__construct();
        $this->userRepository = new UserRepository();
    }

    public function profile() {
        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->getUserDetailsById($userId);

        return $this->render('profile', ['user' => $user]);
    }
}