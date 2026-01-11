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
        
            $action = $segments[0] ?? '';
            $parameters = array_slice($segments, 1);
        
            // regex UUID
            $uuidPattern = '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/';

            if(array_key_exists($action, self::$routes)){
                // standardowe trasy
                $controllerName = self::$routes[$action]['controller'];
                $method = self::$routes[$action]['action'];
        
                $controller = $controllerName::getInstance();
                $controller->$method();
            } elseif($action === 'user' && isset($parameters[0]) && preg_match($uuidPattern, $parameters[0])){
                // dynamiczna trasa z UUID
                $uuid = $parameters[0];
                $controller = UserController::getInstance();
                $controller->profile($uuid); // np. metoda profile($uuid)
            } else {
                // 404
                include 'public/views/404.html';
                echo "<h2>404 - Page Not Found</h2>";
            }
        }        
}