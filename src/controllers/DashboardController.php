<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/CardsRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController {

    private $cardsRepository;

    public function __construct() {
        parent::__construct(); 
        $this->cardsRepository = new CardsRepository();
    }

    public function index() {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $url = "http://$_SERVER[HTTP_HOST]";
            header("Location: {$url}/login");
            exit();
        }

        $cards = $this->cardsRepository->getCardsByUserId($userId);

        return $this->render('dashboard', ["cards" => $cards]);
    }

    public function search() {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if($contentType != 'application/json'){
            http_response_code(415);
            echo json_encode(['status' => 415, 'message' => 'Media type not supported']);
            return;
        }
        
        if (!$this->isPost()) {
            http_response_code(405); // Standardowy kod dla błędnej metody to 405
            echo json_encode(['status' => 405, 'message' => 'Method not allowed']);            
            return;
        }

        header('Content-Type: application/json');
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        // Pobieramy to co użytkownik wpisał, zamiast sztywnego 'heart'
        $searchString = $decoded['search'] ?? '';
        $cards = $this->cardsRepository->getCardByTitle($searchString);
        
        echo json_encode($cards);
    }
}