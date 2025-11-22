<?php
// Variables globales pour la connexion à la base de données
$host = 'localhost';
$dbname = 'e40250u_sae301';
$username = 'root';
$password = '';

/**
 * Fonction pour se connecter à la base de données
 * @return PDO|null Retourne l'objet PDO ou null en cas d'erreur
 */
function connectDB() {
    global $host, $dbname, $username, $password;
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        return null;
    }
}
?>