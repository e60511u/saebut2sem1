<?php
/**
 * Script de vérification rapide
 * Affiche un résumé simple de la configuration
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification - Parking Metz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8A0808;
            border-bottom: 3px solid #8A0808;
            padding-bottom: 10px;
        }
        .test {
            margin: 15px 0;
            padding: 15px;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
        }
        .status {
            font-weight: bold;
            margin-bottom: 5px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        a {
            color: #8A0808;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vérification Rapide - Application Parking Metz</h1>
        
        <?php
        $allOk = true;
        
        // Test 1 : Version PHP
        echo '<div class="test ' . (version_compare(phpversion(), '7.4.0', '>=') ? 'success' : 'error') . '">';
        echo '<div class="status">1. Version PHP</div>';
        echo 'Version actuelle : <code>' . phpversion() . '</code><br>';
        if (version_compare(phpversion(), '7.4.0', '>=')) {
            echo '✓ Version compatible';
        } else {
            echo '✗ Version trop ancienne (minimum 7.4 requis)';
            $allOk = false;
        }
        echo '</div>';
        
        // Test 2 : Extensions PHP
        echo '<div class="test">';
        echo '<div class="status">2. Extensions PHP</div>';
        $extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        $extOk = true;
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            echo ($loaded ? '✓' : '✗') . ' Extension <code>' . $ext . '</code><br>';
            if (!$loaded) {
                $extOk = false;
                $allOk = false;
            }
        }
        echo '</div>';
        
        // Test 3 : Fichiers
        echo '<div class="test">';
        echo '<div class="status">3. Fichiers essentiels</div>';
        $files = [
            'index.php',
            'config/db.php',
            'api/getParkings.php',
            'assets/css/style.css',
            'assets/js/app.js'
        ];
        $filesOk = true;
        foreach ($files as $file) {
            $exists = file_exists(__DIR__ . '/' . $file);
            echo ($exists ? '✓' : '✗') . ' <code>' . $file . '</code><br>';
            if (!$exists) {
                $filesOk = false;
                $allOk = false;
            }
        }
        echo '</div>';
        
        // Test 4 : Connexion BDD
        echo '<div class="test">';
        echo '<div class="status">4. Connexion à la base de données</div>';
        try {
            require_once __DIR__ . '/config/db.php';
            $db = Database::getInstance();
            echo '✓ Connexion réussie<br>';
            
            // Test requête
            $stmt = $db->query("SELECT COUNT(*) as count FROM Utilisateur");
            $result = $stmt->fetch();
            echo '✓ Base de données accessible (' . $result['count'] . ' utilisateur(s))<br>';
        } catch (Exception $e) {
            echo '✗ Erreur : ' . htmlspecialchars($e->getMessage()) . '<br>';
            echo '<small>Vérifiez config/db.php avec vos identifiants</small>';
            $allOk = false;
        }
        echo '</div>';
        
        // Test 5 : API
        echo '<div class="test">';
        echo '<div class="status">5. Endpoints API</div>';
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $baseUrl = rtrim($baseUrl, '/');
        
        $apis = [
            'getParkings.php' => $baseUrl . '/api/getParkings.php',
            'getRoute.php' => $baseUrl . '/api/getRoute.php?lat1=49.1193&lng1=6.1757&lat2=49.1200&lng2=6.1760',
            'getUserPreferences.php' => $baseUrl . '/api/getUserPreferences.php?user_id=1'
        ];
        
        foreach ($apis as $name => $url) {
            $exists = file_exists(__DIR__ . '/api/' . $name);
            if ($exists) {
                echo '✓ <code>' . $name . '</code> existe<br>';
                echo '  <small><a href="' . htmlspecialchars($url) . '" target="_blank">Tester</a></small><br>';
            } else {
                echo '✗ <code>' . $name . '</code> manquant<br>';
                $allOk = false;
            }
        }
        echo '</div>';
        
        // Résumé
        echo '<div class="test ' . ($allOk ? 'success' : 'warning') . '">';
        echo '<div class="status">📊 Résumé</div>';
        if ($allOk) {
            echo '<strong style="color: #28a745;">✓ Tout semble correct !</strong><br>';
            echo '<br><a href="index.php" style="display: inline-block; padding: 10px 20px; background: #8A0808; color: white; border-radius: 5px; text-decoration: none; margin-top: 10px;">Accéder à l\'application</a>';
        } else {
            echo '<strong style="color: #dc3545;">✗ Des problèmes ont été détectés</strong><br>';
            echo 'Consultez GUIDE_DEPLOIEMENT.md pour plus d\'aide.';
        }
        echo '</div>';
        
        // Informations système
        echo '<div class="test info">';
        echo '<div class="status">ℹ️ Informations système</div>';
        echo 'URL de base : <code>' . htmlspecialchars($baseUrl) . '</code><br>';
        echo 'Répertoire : <code>' . htmlspecialchars(__DIR__) . '</code><br>';
        echo 'Serveur : <code>' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu') . '</code><br>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
