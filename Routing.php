<?php

class Routing {
    public static function run(string $path){
        switch($path) {
            case 'dashboard':
                include 'public/views/dashboard.html';
                echo "<h1>DASHBOARD</h1>";
                break;
            case 'login':
                include 'public/views/login.html';
                echo "<h1>LOGIN</h1>";
                break;
            default:
                include 'public/views/404.html';
                echo "<h1>404</h1>";
                break;
        }
    }
}