<?php
/**
 * Script de test de la configuration
 * 
 * Ce fichier permet de vérifier que la configuration est correcte
 * À supprimer en production
 */

// Désactiver l'affichage des erreurs pour ce script de test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de configuration - Application Parking Metz</h1>";

// Test 1 : Version PHP
echo "<h2>1. Version PHP</h2>";
echo "Version PHP : " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<span style='color: green;'>✓ Version PHP compatible</span><br>";
} else {
    echo "<span style='color: red;'>✗ Version PHP trop ancienne (minimum 7.4 requis)</span><br>";
}

// Test 2 : Extensions PHP
echo "<h2>2. Extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green;'>✓ Extension {$ext} chargée</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Extension {$ext} manquante</span><br>";
    }
}

// Test 3 : Connexion à la base de données
echo "<h2>3. Connexion à la base de données</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    $db = Database::getInstance();
    echo "<span style='color: green;'>✓ Connexion à la base de données réussie</span><br>";
    
    // Test d'une requête simple
    $stmt = $db->query("SELECT COUNT(*) as count FROM Utilisateur");
    $result = $stmt->fetch();
    echo "Nombre d'utilisateurs dans la base : " . $result['count'] . "<br>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</span><br>";
}

// Test 4 : Accès aux fichiers
echo "<h2>4. Vérification des fichiers</h2>";
$required_files = [
    'index.php',
    'config/db.php',
    'api/getParkings.php',
    'api/getRoute.php',
    'api/getUserPreferences.php',
    'includes/header.php',
    'includes/footer.php',
    'assets/css/style.css',
    'assets/js/api_client.js',
    'assets/js/map.js',
    'assets/js/app.js'
];

foreach ($required_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<span style='color: green;'>✓ {$file}</span><br>";
    } else {
        echo "<span style='color: red;'>✗ {$file} manquant</span><br>";
    }
}

// Test 5 : Permissions d'écriture
echo "<h2>5. Permissions</h2>";
$writable_dirs = ['config', 'api'];
foreach ($writable_dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_writable($path)) {
        echo "<span style='color: green;'>✓ Dossier {$dir} accessible en écriture</span><br>";
    } else {
        echo "<span style='color: orange;'>⚠ Dossier {$dir} non accessible en écriture (peut être normal)</span><br>";
    }
}

echo "<hr>";
echo "<p><strong>Test terminé.</strong> Si tous les tests sont verts, l'application devrait fonctionner correctement.</p>";
echo "<p><em>Note : Supprimez ce fichier en production pour des raisons de sécurité.</em></p>";
