<?php
// Variables globales pour la connexion à la base de données
$db_host = 'localhost';
$db_dbname = 'e40250u_sae301';
$db_username = 'user_a';
$db_password = 'a';

/**
 * Fonction pour se connecter à la base de données
 * @return PDO|null Retourne l'objet PDO ou null en cas d'erreur
 */
function connectDB() {
    global $db_host, $db_dbname, $db_username, $db_password;
    
    try {
        // Utiliser une chaîne vide explicite si le mot de passe est vide
        $dsn = "mysql:host=$db_host;dbname=$db_dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        $pdo = new PDO($dsn, $db_username, $db_password, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        return null;
    }
}
?>