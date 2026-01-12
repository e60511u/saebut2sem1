<?php
/**
 * Configuration de la base de données
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Cette classe a une seule responsabilité : gérer la connexion à la base de données
 * 
 * Pattern utilisé : Singleton pour garantir une seule instance de connexion
 */

class Database {
    /**
     * Instance unique de la classe (Singleton)
     */
    private static $instance = null;
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        // Empêche l'instanciation directe
    }
    
    /**
     * Empêche le clonage de l'instance
     */
    private function __clone() {
        // Empêche le clonage
    }
    
    /**
     * Empêche la désérialisation
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Récupère l'instance unique de la connexion PDO
     * 
     * @return PDO Instance de la connexion à la base de données
     * @throws Exception Si la connexion échoue
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                // Configuration de la base de données
                // Configuration pour le serveur IUT
                $host = 'devbdd.iutmetz.univ-lorraine.fr';
                $dbname = 'e40250u_sae301';
                $username = 'e40250u_appli';
                $password = '32408231';
                
                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$instance = new PDO($dsn, $username, $password, $options);
                
            } catch (PDOException $e) {
                error_log("Erreur de connexion à la base de données : " . $e->getMessage());
                throw new Exception("Impossible de se connecter à la base de données");
            }
        }
        
        return self::$instance;
    }
}
