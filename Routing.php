<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';


class Routing {

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index"
        ]

        ];

        public static function run(string $path){
            $path = trim($path, '/');
            $segments = explode('/', $path);
            
            // Pobierz pierwszy segment (nazwa routingu)
            $action = $segments[0] ?? '';
            
            // Pobierz parametry (wszystko po pierwszym segmencie)
            $parameters = array_slice($segments, 1);
    
            switch($action){
                case 'dashboard':
                    $controller = Routing::$routes[$action]['controller'];
                    $method = Routing::$routes[$action]['action'];
    
                    $controller = $controller::getInstance();
                    $controller->$method();
                    break;
                case 'register':
                case 'login':
                    $controller = Routing::$routes[$action]['controller'];
                    $method = Routing::$routes[$action]['action'];
                    
                    $controller = $controller::getInstance();
                    $controller->$method();
                    break;
                default:
                    include 'public/views/404.html';
                    echo "<h2>404</h2>";
                    break;
            }
    }
}