<?php
/**
 * Test simple pour diagnostiquer les erreurs
 * Ce fichier teste les éléments de base sans dépendances complexes
 */

// Activer l'affichage des erreurs temporairement
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Simple</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .ok { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test Simple - Diagnostic</h1>
    
    <h2>1. Test PHP de base</h2>
    <?php
    echo '<p class="ok">✓ PHP fonctionne (version ' . phpversion() . ')</p>';
    ?>
    
    <h2>2. Test des fichiers</h2>
    <?php
    $files = [
        'index.php',
        'config/db.php',
        'includes/header.php',
        'includes/footer.php',
        'api/getParkings.php'
    ];
    
    foreach ($files as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            echo '<p class="ok">✓ ' . $file . ' existe</p>';
        } else {
            echo '<p class="error">✗ ' . $file . ' MANQUANT</p>';
        }
    }
    ?>
    
    <h2>3. Test des chemins</h2>
    <?php
    echo '<p>Répertoire courant : <code>' . __DIR__ . '</code></p>';
    echo '<p>Script name : <code>' . $_SERVER['SCRIPT_NAME'] . '</code></p>';
    echo '<p>Base path calculé : <code>' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '</code></p>';
    ?>
    
    <h2>4. Test des includes</h2>
    <?php
    try {
        $testPath = __DIR__ . '/includes/header.php';
        if (file_exists($testPath)) {
            echo '<p class="ok">✓ Le fichier header.php est accessible</p>';
        } else {
            echo '<p class="error">✗ Le fichier header.php n\'existe pas</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
    
    <h2>5. Test de connexion BDD (sans afficher les erreurs)</h2>
    <?php
    try {
        require_once __DIR__ . '/config/db.php';
        $db = Database::getInstance();
        echo '<p class="ok">✓ Connexion à la base de données réussie</p>';
    } catch (Exception $e) {
        echo '<p class="error">✗ Erreur BDD : ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><small>Note : Cette erreur est normale si la BDD n\'est pas configurée</small></p>';
    }
    ?>
    
    <h2>6. Test des extensions PHP</h2>
    <?php
    $extensions = ['pdo', 'pdo_mysql', 'json'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo '<p class="ok">✓ Extension ' . $ext . ' chargée</p>';
        } else {
            echo '<p class="error">✗ Extension ' . $ext . ' manquante</p>';
        }
    }
    ?>
    
    <hr>
    <p><strong>Si tous les tests sont OK, essayez d'accéder à <a href="index.php">index.php</a></strong></p>
    <p><small>Si ce fichier fonctionne mais pas index.php, le problème vient probablement du fichier index.php ou des includes</small></p>
</body>
</html>
