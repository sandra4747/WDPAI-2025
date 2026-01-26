<?php

require_once 'AppController.php';
require_once __DIR__ .'/../repository/GoalRepository.php';

class GoalController extends AppController {

    const MAX_FILE_SIZE = 1024*1024;
    const SUPPORTED_TYPES = ['image/png', 'image/jpeg', 'image/jpg'];
    const UPLOAD_DIRECTORY = '/../public/uploads/';

    private $messages = [];
    private $goalRepository;

    public function __construct()
    {
        parent::__construct();
        $this->goalRepository = new GoalRepository();
    }

    public function addGoal()
    {
        if ($this->isPost()) {
            
            // Obsługa pliku
            $imagePath = null;
            if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                
                if ($this->validate($_FILES['image'])) {
                    $imagePath = time() . '_' . $_FILES['image']['name']; 
                    
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'], 
                        dirname(__DIR__).self::UPLOAD_DIRECTORY . $imagePath
                    );
                } else {
                    $categories = $this->goalRepository->getCategories();
                    return $this->render('add_goal', [
                        'messages' => $this->messages,
                        'categories' => $categories
                    ]);
                }
            }

            // Zapis do bazy 
            $userId = $_SESSION['user_id'] ?? null; 

            if ($userId) {
                $this->goalRepository->addGoal($_POST, $imagePath, $userId);
                
                $url = "http://" . $_SERVER['HTTP_HOST'];
                header("Location: {$url}/dashboard");
                exit(); 
            }
        }
        
        $categories = $this->goalRepository->getCategories();

        return $this->render('add_goal', [
            'messages' => $this->messages,
            'categories' => $categories
        ]);
    }

    private function validate(array $file): bool
    {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->messages[] = 'Plik jest za duży (max 1MB).';
            return false;
        }

        if (isset($file['type']) && !in_array($file['type'], self::SUPPORTED_TYPES)) {
            $this->messages[] = 'Nieobsługiwany format pliku.';
            return false;
        }

        return true;
    }
}