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
            http_response_code(403);
            include 'public/views/403.html';
            exit();
        }
    }

    public function users() {
        $users = $this->userRepository->getUsersDetails();
        return $this->render('admin', ['users' => $users]);
    }

    public function deleteUser() {
        if (!$this->isPost()) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/admin");
            exit();
        }

        $this->userRepository->deleteUser((int)$_POST['id']);
        
        $url = "http://$_SERVER[HTTP_HOST]";
        header("Location: {$url}/admin");
        exit();
    }
}
