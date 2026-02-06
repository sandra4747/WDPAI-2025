<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/GoalRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController {

    private $goalsRepository;

    public function __construct() {
        parent::__construct(); 
        $this->goalsRepository = new GoalRepository();
        $this->checkUserOnly();

    }

    public function index() {

        $this->checkLogin();
        
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $goals = $this->goalsRepository->getGoalsByUserId($userId);
        $totalProgress = $this->goalsRepository->getTotalProgress($userId); 

    return $this->render('dashboard', [
        "goals" => $goals, 
        "totalProgress" => $totalProgress 
    ]);
    }

    public function search() {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if($contentType != 'application/json'){
            http_response_code(415);
            echo json_encode(['status' => 415, 'message' => 'Media type not supported']);
            return;
        }
        
        if (!$this->isPost()) {
            http_response_code(405); 
            echo json_encode(['status' => 405, 'message' => 'Method not allowed']);            
            return;
        }

        header('Content-Type: application/json');
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        $searchString = $decoded['search'] ?? '';
        $goals = $this->goalsRepository->getGoalByTitle($searchString);
        
        echo json_encode($goals);
    }
}