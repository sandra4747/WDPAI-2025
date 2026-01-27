<?php
ini_set('session.cookie_httponly', 1); // C3: Cookie ma flagę HttpOnly
ini_set('session.cookie_secure', 1);   // D3: Tylko przez HTTPS (wymaga SSL)
ini_set('session.cookie_samesite', 'Lax'); // E3: Ochrona przed CSRF
session_start();

$timeout = 900; // 15 minut w sekundach

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: /login");
    exit();
}
$_SESSION['last_activity'] = time(); 

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

Routing::run($path);


