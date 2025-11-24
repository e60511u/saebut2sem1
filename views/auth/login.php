<?php
$pageTitle = 'Connexion - Parking App';
$additionalHead = '<link rel="stylesheet" href="assets/css/login.css">';

ob_start();
?>
<div class="login-container">
    <h1>🅿️ Parking App</h1>
    <p class="subtitle">Connectez-vous pour accéder à votre compte</p>
    
    <?php if (isset($error) && $error): ?>
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
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
