<?php
// Variables globales pour la connexion à la base de données
$host = 'localhost';
$dbname = 'e40250u_sae301';
$username = 'user_a';
$password = 'a';

/**
 * Fonction pour se connecter à la base de données
 * @return PDO|null Retourne l'objet PDO ou null en cas d'erreur
 */
function connectDB() {
    global $host, $dbname, $username, $password;
    
    try {
        // Utiliser une chaîne vide explicite si le mot de passe est vide
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "Connexion à la base de données réussie !<br>";
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        echo "Erreur de connexion : " . $e->getMessage() . "<br>";
        return null;
    }
}
?>