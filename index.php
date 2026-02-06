<?php
ini_set('session.cookie_httponly', 1); // Chroni przed kradzieżą ciasteczka przez JavaScript (XSS)
ini_set('session.cookie_secure', 1);   // Tylko przez HTTPS (wymaga SSL)
ini_set('session.cookie_samesite', 'Lax'); // Chroni przed CSRF
session_start();

// Wylogowanie po 15 minutach
$timeout = 900; // 15 minut w sekundach

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: /login");
    exit();
}
$_SESSION['last_activity'] = time(); 

// Uruchomienie aplikacji z Globalną Obsługą Błędów Krytycznych (Baza, PHP, Kod)
require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

try {
    Routing::run($path);
} catch (Throwable $e) {
    
    error_log("Critical Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    http_response_code(500);

    include 'public/views/500.html';
}


