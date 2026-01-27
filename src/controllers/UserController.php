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

    public function updateProfile() {
        if (!$this->isPost()) { // A2: Tylko POST
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
    
        // LINIA 34: Nazwa musi być identyczna jak w Repository i mieć 5 argumentów!
        $this->userRepository->updateUserProfile($userId, $name, $surname, $email, $avatarUrl);
    
        return $this->render('profile', [
            'user' => $this->userRepository->getUserDetailsById($userId),
            'messages' => ['Profil został pomyślnie zaktualizowany!']
        ]);
    }
}