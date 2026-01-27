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

    public function editGoal()
    {
        if ($this->isPost()) {
            $id = $_POST['id'];
            $this->goalRepository->updateGoal($id, $_POST);
            
            header("Location: /dashboard"); 
            exit();
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dashboard");
            exit();
        }

        $goal = $this->goalRepository->getGoalById($id);
        $categories = $this->goalRepository->getCategories(); 

        return $this->render('edit_goal', [
            'goal' => $goal, 
            'categories' => $categories
        ]);
    }

    public function addFunds()
    {
        // Sprawdzenie Fetch API
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
        if ($contentType === 'application/json') {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
    
            $goalId = (int)$decoded['goal_id'];
            $amount = (float)$decoded['amount'];
    
            if ($goalId && $amount > 0) {
                
                $result = $this->goalRepository->depositFunds($goalId, $amount);
                $newGoalPercent = $result['new_progress'];
    
                $newTotalProgress = $this->goalRepository->getTotalProgress($_SESSION['user_id']);
    
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'newGoalPercent' => (int)$newGoalPercent,
                    'newTotalPercent' => (int)$newTotalProgress
                ]);
                exit();
            }
        }
    }

    public function deleteGoal()
    {
        if (!$this->isPost()) {
            header("Location: /dashboard");
            exit();
        }
    
        $id = (int)$_POST['id'];
        
        $userId = $_SESSION['user_id'] ?? null;
    
        if ($id && $userId) {
             $this->goalRepository->deleteGoal($id, $userId);
        }
    
        header("Location: /dashboard");
        exit();
    }

    // Endpoint dla Fetch API - pobiera szczegóły celu i historię
    public function getGoalDetails()
    {
        // Sprawdzamy czy to zapytanie JSON
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === 'application/json') {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            $goalId = $decoded['id'] ?? null;

            if ($goalId) {
                $goal = $this->goalRepository->getGoalById($goalId);
                $logs = $this->goalRepository->getGoalLogs($goalId);

                header('Content-Type: application/json');
                echo json_encode([
                    'goal' => $goal,
                    'logs' => $logs
                ]);
                exit();
            }
        }
    }

    public function gallery()
    {
        // 1. Sprawdzamy czy użytkownik jest zalogowany
        // (zakładam, że trzymasz user_id w sesji, tak jak w dashboardzie)
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header("Location: /login");
            exit();
        }

        // 2. Pobieramy cele użytkownika (żeby wyświetlić je w siatce)
        // Używamy tej samej metody co w Dashboardzie
        $goals = $this->goalRepository->getGoalsByUserId($userId);

        // 3. Wyświetlamy widok galerii
        $this->render('gallery', ['goals' => $goals]);
    }
}