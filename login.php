<?php
session_start();
require_once 'includes/auth.php';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    header('Location: app.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($identifier) && !empty($password)) {
        $user = loginUser($identifier, $password);
        
        if ($user) {
            initUserSession($user);
            header('Location: app.php');
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect';
        }
    } else {
        $error = 'Veuillez remplir tous les champs';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Parking App</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>🅿️ Parking App</h1>
        <p class="subtitle">Connectez-vous pour accéder à votre compte</p>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="identifier">Email ou Pseudo</label>
                <input type="text" id="identifier" name="identifier" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Se connecter</button>
        </form>
        
        <div class="register-link">
            Pas encore de compte ? <a href="register.php">Créer un compte</a>
        </div>
    </div>
</body>
</html>
