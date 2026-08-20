<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists('Database')) {
    class Database {
        private static $instance = null;
        private $conn;

        private function __construct() {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->safeLoad();

            $servername = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';
            $dbname = $_ENV['DB_NAME'] ?? 'test';

            mysqli_report(MYSQLI_REPORT_OFF);
            $this->conn = new mysqli($servername, $username, $password, $dbname);

            if ($this->conn->connect_error) {
                die("Connexion échouée: " . $this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        }

        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new Database();
            }
            return self::$instance;
        }

        public function getConnection() {
            return $this->conn;
        }
    }
}

// Fournir $conn et les variables globales pour la compatibilité avec tous les scripts existants
$servername = $_ENV['DB_HOST'] ?? '127.0.0.1';
$username   = $_ENV['DB_USER'] ?? 'root';
$password   = $_ENV['DB_PASS'] ?? '';
$dbname     = $_ENV['DB_NAME'] ?? 'courriers_db';

$conn = Database::getInstance()->getConnection();
?>
