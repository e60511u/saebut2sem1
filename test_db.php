<?php
/**
 * Script de test de connexion à la base de données
 */

require_once __DIR__ . '/config/db.php';

echo "=== Test de connexion à la base de données ===\n\n";

try {
    $pdo = connectDB();
    
    if ($pdo === null) {
        echo "❌ ÉCHEC : La fonction connectDB() a retourné null\n";
        echo "Vérifiez les logs d'erreur PHP\n";
    } else {
        echo "✅ SUCCÈS : Connexion réussie !\n\n";
        
        // Test de requête
        $stmt = $pdo->query("SELECT DATABASE() as db_name");
        $result = $stmt->fetch();
        echo "Base de données active : " . $result['db_name'] . "\n\n";
        
        // Lister les tables
        echo "Tables disponibles :\n";
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch()) {
            echo "  - " . $row[array_keys($row)[0]] . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}
?>
