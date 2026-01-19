<?php
require_once "config.php";
class Database {
    private static ?Database $instance = null; // jedyna instancja
    private ?PDO $conn = null;

    private string $username;
    private string $password;
    private string $host;
    private string $database;

    // Prywatny konstruktor – nikt nie może zrobić new Database()
    private function __construct() {
        $this->username = USERNAME;
        $this->password = PASSWORD;
        $this->host = HOST;
        $this->database = DATABASE;
    }

    // Publiczna metoda do pobrania instancji
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connect(): PDO {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "pgsql:host={$this->host};port=5432;dbname={$this->database}",
                    $this->username,
                    $this->password,
                    ["sslmode" => "prefer"]
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return $this->conn;
    }

    public function disconnect(): void {
        $this->conn = null;
    }
    
}