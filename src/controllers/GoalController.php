<?php

require_once 'AppController.php';
require_once __DIR__ .'/../repository/GoalRepository.php';
require_once __DIR__ .'/../repository/BadgeRepository.php'; 
require_once __DIR__ .'/../dto/GoalDTO.php';

class GoalController extends AppController {

    const MAX_FILE_SIZE = 1024*1024;
    const SUPPORTED_TYPES = ['image/png', 'image/jpeg', 'image/jpg'];
    const UPLOAD_DIRECTORY = '/../public/uploads/';

    private $messages = [];
    private $goalRepository;
    private $badgeRepository;

    public function __construct()
    {
        $this->checkLogin();
        parent::__construct();

        $this->checkUserOnly();
        
        $this->goalRepository = new GoalRepository();
        $this->badgeRepository = new BadgeRepository();
    }

    public function addGoal()
    {
        if ($this->isPost()) {
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

            $userId = $_SESSION['user_id'] ?? null; 
            if ($userId) {
                $goalData = $_POST;
                $goalData['user_id'] = $userId;
                $goalData['image_path'] = $imagePath;

                $goalDTO = new GoalDTO($goalData);
                $this->goalRepository->addGoal($goalDTO);
                
                $this->badgeRepository->checkAchievements($userId);
                header("Location: /dashboard");
                exit(); 
            }
        }
        
        $categories = $this->goalRepository->getCategories();
        return $this->render('add_goal', [
            'messages' => $this->messages,
            'categories' => $categories
        ]);
    }

    public function editGoal()
    {
        if ($this->isPost()) {
            $id = (int) $_POST['id'];
            
            $currentGoal = $this->goalRepository->getGoalById($id);
            $imagePath = $currentGoal->imagePath;

            if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                if ($this->validate($_FILES['image'])) {
                    $imagePath = time() . '_' . $_FILES['image']['name']; 
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'], 
                        dirname(__DIR__).self::UPLOAD_DIRECTORY . $imagePath
                    );
                }
            }

            $data = $_POST;
            $data['amount'] = $_POST['target_amount'] ?? 0;
            $data['image_path'] = $imagePath;

            $goalDTO = new GoalDTO($data);
            $this->goalRepository->updateGoal($id, $goalDTO);
            
            header("Location: /dashboard"); 
            exit();
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: /dashboard");
            exit();
        }

        $goal = $this->goalRepository->getGoalById((int)$id);
        $categories = $this->goalRepository->getCategories();

        if (!$goal) {
            header("Location: /dashboard");
            exit();
        }

        return $this->render('edit_goal', [
            'goal' => $goal, 
            'categories' => $categories,
            'messages' => $this->messages
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

    public function gallery()
    {
        $userId = $_SESSION['user_id'] ?? null;
        $goals = $this->goalRepository->getGoalsByUserId($userId);
        $this->render('gallery', ['goals' => $goals]);
    }

    public function addFunds()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === 'application/json') {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            $goalId = (int)$decoded['goal_id'];
            $amount = (float)$decoded['amount'];
    
            if ($goalId && $amount > 0) {
                $result = $this->goalRepository->depositFunds($goalId, $amount);

                $this->badgeRepository->checkAchievements($_SESSION['user_id']);

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

    public function getGoalDetails()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === 'application/json') {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            $goalId = $decoded['id'] ?? null;
            if ($goalId) {
                $goal = $this->goalRepository->getGoalById($goalId);
                $logs = $this->goalRepository->getGoalLogs($goalId);
                header('Content-Type: application/json');
                echo json_encode(['goal' => $goal, 'logs' => $logs]);
                exit();
            }
        }
    }
}