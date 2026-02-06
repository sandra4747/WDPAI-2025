<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';
require_once __DIR__.'/../repository/BadgeRepository.php';
require_once __DIR__.'/../dto/UserDTO.php';


class UserController extends AppController {

    private $userRepository;
    private $badgeRepository;

    public function __construct() {
        $this->checkLogin(); // Ochrona sesji
        parent::__construct();

        $this->checkUserOnly();

        $this->userRepository = new UserRepository();
        $this->badgeRepository = new BadgeRepository();
    }

    public function profile() {
        $userId = $_SESSION['user_id'];
        $user = $this->userRepository->getUserDetailsById($userId);
        $badges = $this->badgeRepository->getUserBadges($userId);

        return $this->render('profile', [
            'user' => $user,
            'badges' => $badges 
        ]);
    }

    public function updateProfile() {
        if (!$this->isPost()) { 
            header("Location: /profile");
            exit();
        }
    
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $surname = $_POST['surname'] ?? '';
        $email = $_POST['email'] ?? '';
    
        // Obsługa przesyłania avatara
        $avatarUrl = null;
        if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $avatarUrl = $_FILES['file']['name'];
            move_uploaded_file(
                $_FILES['file']['tmp_name'], 
                dirname(__DIR__).'/../public/uploads/'.$avatarUrl
            );
        } else {
            // Jeśli nie ma nowego pliku, pobierz obecny avatar z bazy
            $user = $this->userRepository->getUserDetailsById($userId);
            $avatarUrl = $user->avatarUrl;
        }
    
        $this->userRepository->updateUserProfile($userId, $name, $surname, $email, $avatarUrl);
        $badges = $this->badgeRepository->getUserBadges($userId);
    
        return $this->render('profile', [
            'user' => $this->userRepository->getUserDetailsById($userId),
            'badges' => $badges,
            'messages' => ['Profil został pomyślnie zaktualizowany!']
        ]);
    }
}