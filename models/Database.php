<?php
/**
 * Modèle Database
 * Gère la connexion à la base de données (Singleton)
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        require_once __DIR__ . '/../config/db.php';
        $this->pdo = connectDB();
        
        if ($this->pdo === null) {
            throw new Exception("Échec de la connexion à la base de données. Vérifiez vos identifiants dans config/db.php");
        }
    }
    
    /**
     * Obtenir l'instance unique de la base de données
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     * @return PDO|null
     */
    public function getConnection() {
        return $this->pdo;
    }
}
?>
