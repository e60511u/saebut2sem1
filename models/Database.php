<?php
/**
 * Modèle Database
 * Gère la connexion à la base de données (Singleton)
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $config_path = __DIR__ . '/../config/db.php';
        
        if (!file_exists($config_path)) {
            throw new Exception("Fichier de configuration introuvable : " . $config_path);
        }
        
        require_once $config_path;
        
        if (!function_exists('connectDB')) {
            throw new Exception("La fonction connectDB() n'est pas définie dans config/db.php");
        }
        
        $this->pdo = connectDB();
        
        if ($this->pdo === null) {
            throw new Exception("Échec de la connexion à la base de données. Vérifiez vos identifiants dans config/db.php et les logs d'erreur PHP.");
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
