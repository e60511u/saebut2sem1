<?php
/**
 * Script de vérification de l'installation
 * Vérifie que tous les fichiers nécessaires sont présents et accessibles
 */

// Configuration
$requiredFiles = [
    'config/db.php' => 'Configuration de base de données',
    'includes/auth.php' => 'Fonctions d\'authentification',
    'includes/user.php' => 'Fonctions de gestion utilisateur',
    'assets/css/style.css' => 'CSS principal',
    'assets/css/login.css' => 'CSS connexion',
    'assets/css/register.css' => 'CSS inscription',
    'assets/css/user_settings.css' => 'CSS paramètres',
    'assets/js/script.js' => 'JavaScript principal',
    'app.php' => 'Page principale',
    'login.php' => 'Page de connexion',
    'register.php' => 'Page d\'inscription',
    'user_settings.php' => 'Page de paramètres'
];

$requiredFunctions = [
    'includes/auth.php' => ['loginUser', 'registerUser', 'initUserSession', 'isLoggedIn', 'requireLogin'],
    'includes/user.php' => ['getUserById', 'updateUser', 'getUserVehicles', 'addVehicle', 'deleteVehicle', 
                            'getUserFavorites', 'addFavorite', 'deleteFavorite', 'getVehicleTypes', 'getMotorisations'],
    'config/db.php' => ['connectDB']
];

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vérification de l'Installation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #8A0808;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
        }
        h2 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #8A0808;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        .check-item.error {
            background: #ffebee;
            border-left: 4px solid #f44336;
        }
        .status {
            font-weight: bold;
        }
        .status.success { color: #4caf50; }
        .status.error { color: #f44336; }
        .summary {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary h3 {
            color: #8A0808;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            background: #8A0808;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
        }
        .btn:hover {
            background: #B71C1C;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ Vérification de l'Installation</h1>
        <p class='subtitle'>Contrôle de l'intégrité de la structure du projet</p>";

// Vérification des fichiers
echo "<div class='section'>
        <h2>📁 Fichiers Requis</h2>";

$filesOk = 0;
$filesTotal = count($requiredFiles);

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($file);
    $class = $exists ? 'success' : 'error';
    $status = $exists ? '✓ OK' : '✗ MANQUANT';
    $statusClass = $exists ? 'success' : 'error';
    
    if ($exists) $filesOk++;
    
    echo "<div class='check-item $class'>
            <span><strong>$file</strong> - $description</span>
            <span class='status $statusClass'>$status</span>
          </div>";
}

echo "</div>";

// Vérification des fonctions
echo "<div class='section'>
        <h2>⚙️ Fonctions Disponibles</h2>";

$functionsOk = 0;
$functionsTotal = 0;

foreach ($requiredFunctions as $file => $functions) {
    if (file_exists($file)) {
        require_once $file;
        
        foreach ($functions as $function) {
            $functionsTotal++;
            $exists = function_exists($function);
            $class = $exists ? 'success' : 'error';
            $status = $exists ? '✓ OK' : '✗ MANQUANT';
            $statusClass = $exists ? 'success' : 'error';
            
            if ($exists) $functionsOk++;
            
            echo "<div class='check-item $class'>
                    <span><strong>$function()</strong> dans $file</span>
                    <span class='status $statusClass'>$status</span>
                  </div>";
        }
    }
}

echo "</div>";

// Résumé
$allOk = ($filesOk === $filesTotal) && ($functionsOk === $functionsTotal);
$summaryClass = $allOk ? 'success' : 'error';

echo "<div class='summary'>
        <h3>📊 Résumé</h3>
        <p><strong>Fichiers :</strong> $filesOk/$filesTotal OK</p>
        <p><strong>Fonctions :</strong> $functionsOk/$functionsTotal OK</p>";

if ($allOk) {
    echo "<p style='color: #4caf50; font-weight: bold; margin-top: 15px;'>
            ✅ Installation complète et opérationnelle !
          </p>
          <a href='index.html' class='btn'>Accéder à l'application</a>";
} else {
    echo "<p style='color: #f44336; font-weight: bold; margin-top: 15px;'>
            ⚠️ Certains fichiers ou fonctions sont manquants. Veuillez vérifier l'installation.
          </p>";
}

echo "</div>
    </div>
</body>
</html>";
?>
