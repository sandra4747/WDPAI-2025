<?php
// .env powinno byc
require_once "config.php";
// to powinien byc singleton
class Database {
    private $username;
    private $password;
    private $host;
    private $database;
    // private $conn;

    public function __construct()
    {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    // trzeba dopisac metode disconnect  this->conn = null;
    public function connect()
    {
        try {
            $conn = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode"  => "prefer"]
            );

            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        }
        catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage()); // przekierowac na strone z bledem
        }
    }
}