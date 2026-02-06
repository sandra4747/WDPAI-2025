<?php

require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/GoalController.php';
require_once 'src/controllers/UserController.php';
require_once 'src/controllers/AdminController.php';


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
        "logout" => [ 
            "controller" => "SecurityController",
            "action" => "logout"
        ],
        "dashboard" => [
            "controller" => "DashboardController",
            "action" => "index"
        ],
        "addGoal" => [          
            "controller" => "GoalController",
            "action" => "addGoal"
        ],
        "addFunds" => [
            "controller" => "GoalController",
            "action" => "addFunds"
        ],
        'editGoal' => [
            'controller' => 'GoalController',
            'action' => 'editGoal'
        ],
        'deleteGoal' => [
            'controller' => 'GoalController',
            'action' => 'deleteGoal'
        ], 
        'getGoalDetails' => [
            'controller' => 'GoalController',
             'action' => 'getGoalDetails'
        ],
        'gallery' => [
            'controller' => 'GoalController',
             'action' => 'gallery'
        ],
        'profile' => [
            'controller' => 'UserController',
             'action' => 'profile'
        ],
        'updateProfile' => [
            'controller' => 'UserController',
            'action' => 'updateProfile'
        ],
        'admin' => [
            'controller' => 'AdminController',
            'action' => 'users'
        ],
        'deleteUser' => [
            'controller' => 'AdminController',
            'action' => 'deleteUser'
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
                $controller->profile($uuid); 
            } else {
                // 404
                http_response_code(404);
                include 'public/views/404.html';
            }
        }        
}