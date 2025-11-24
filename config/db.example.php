<?php
/**
 * EXEMPLE DE CONFIGURATION DE BASE DE DONNÉES
 * 
 * Copiez ce fichier vers config/db.php et modifiez les valeurs selon votre environnement
 * 
 * IMPORTANT : config/db.php ne doit JAMAIS être commité dans Git
 * Il est ajouté au .gitignore pour protéger vos identifiants
 */

// Variables globales pour la connexion à la base de données
$db_host = 'localhost';           // Hôte de la base de données
$db_dbname = 'e40250u_sae301';   // Nom de la base de données
$db_username = 'user_a';          // Nom d'utilisateur
$db_password = 'a';               // Mot de passe

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
