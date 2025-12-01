<?php

require_once 'AppController.php';
//require_once 'UserRepository.php'; 
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController {

    public function index(?int $id = null) {
        $cards = [];

        if ($id !== null) {
 
        }

        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();

        return $this->render('dashboard', ["cards" => $cards]);
    }
}
