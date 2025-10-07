<?php

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // Vérifier que toutes les variables d'environnement sont présentes
        $requiredEnvVars = ['MYSQL_HOST', 'MYSQL_DATABASE', 'MYSQL_USER', 'MYSQL_PASSWORD'];
        
        foreach ($requiredEnvVars as $var) {
            if (!isset($_ENV[$var]) || empty($_ENV[$var])) {
                die("Variable d'environnement manquante : $var");
            }
        }
        
        $host = $_ENV['MYSQL_HOST'];
        $dbname = $_ENV['MYSQL_DATABASE'];
        $username = $_ENV['MYSQL_USER'];
        $password = $_ENV['MYSQL_PASSWORD'];
        
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

function getDatabase() {
    return Database::getInstance()->getConnection();
}
?>
