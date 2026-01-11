<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/CardsRepository.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class DashboardController extends AppController {

    private $cardsRepository;

    public function __construct() {
        $this->cardsRepository = new CardsRepository();
    }

    public function index(?int $id = null) {
        $cards = [];

        if ($id !== null) {
 
        }

        $userRepository = new UserRepository();
        $users = $userRepository->getUsers();

        return $this->render('dashboard', ["cards" => $cards]);
    }

    public function search() {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

        if($contentType != 'application/json'){
            http_response_code(415);
            echo json_encode([
                'status' => 415,
                'message' => 'Media type not supported'
            ]);
            return;
        }
        if (!$this->isPost()) {
            http_response_code(485);
            echo json_encode([
                'status' => 485,
                'message' => 'Method not allowed'
            ]);            
            return;
        }
        header('Content-Type: application/json');
        http_response_code(200);

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        $cards = $this->cardsRepository->getCardByTitle('heart');
        echo json_encode($cards);
    }
}
