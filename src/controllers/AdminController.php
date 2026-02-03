<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../dto/AdminUserDTO.php';

class AdminController extends AppController {
    private $userRepository;

    public function __construct() {
        parent::__construct();
        $this->userRepository = new UserRepository();
        $this->checkAdmin();
    }

    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'ROLE_ADMIN') {
            $this->render('error_403');
            exit();
        }
    }

    public function users() {
        $users = $this->userRepository->getUsersDetails();
        return $this->render('admin', ['users' => $users]);
    }

    public function deleteUser() {
        if (!$this->isPost()) {
            header("Location: /admin");
            exit();
        }
        $this->userRepository->deleteUser((int)$_POST['id']);
        header("Location: /admin");
    }
}
