<?php
/**
 * Version de test simplifiée de index.php
 * Pour isoler les problèmes
 */

// Activer l'affichage des erreurs temporairement
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test</title></head><body>";
echo "<h1>Test Index</h1>";

// Test 1 : PHP fonctionne
echo "<p>✓ PHP fonctionne</p>";

// Test 2 : Vérifier les fichiers
echo "<h2>Vérification des fichiers</h2>";
$files = [
    'includes/header.php',
    'includes/footer.php',
    'config/db.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p>✓ $file existe</p>";
    } else {
        echo "<p style='color:red;'>✗ $file MANQUANT</p>";
    }
}

// Test 3 : Tester l'inclusion de header
echo "<h2>Test inclusion header</h2>";
try {
    $headerPath = __DIR__ . '/includes/header.php';
    if (file_exists($headerPath)) {
        echo "<p>✓ Tentative d'inclusion de header.php...</p>";
        // Ne pas inclure pour l'instant, juste vérifier
        echo "<p>✓ Le fichier est accessible</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>✗ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4 : Tester la connexion BDD (sans bloquer)
echo "<h2>Test connexion BDD</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    $db = Database::getInstance();
    echo "<p style='color:green;'>✓ Connexion BDD OK</p>";
} catch (Exception $e) {
    echo "<p style='color:orange;'>⚠ Erreur BDD (normal si pas configuré) : " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Si tout est OK ici, le problème vient peut-être du fichier index.php original</strong></p>";
echo "<p><a href='index.php'>Essayer index.php</a></p>";
echo "</body></html>";
