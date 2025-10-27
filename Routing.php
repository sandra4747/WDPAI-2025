<?php

require_once 'src/controllers/SecurityController.php';

class Routing {

    public static $routes = [
        "login" => [
            "controller" => "SecurityController",
            "action" => "login"
        ],
        "register" => [
            "controller" => "SecurityController",
            "action" => "register"
        ]

        ];

    public static function run(string $path){
        switch($path){
            case 'dashboard':

                include 'public/views/dashboard.html';
                echo "<h2>Dashboard</h2>";
                break;
            case 'login':
            case 'register':
                $controller = Routing::$routes[$path]["controller"];
                $action = Routing::$routes[$path]["action"];

                $controllerObj = new $controller();
                $controllerObj->$action();
                break;
            default:
                include 'public/views/404.html';
                echo "<h2>404</h2>";
                break;
        }
    }
}