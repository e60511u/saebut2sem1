<?php
$pageTitle = 'Inscription - Parking App';
$additionalHead = '<link rel="stylesheet" href="assets/css/register.css">';

ob_start();
?>
<div class="register-container">
    <h1>🅿️ Parking App</h1>
    <p class="subtitle">Créez votre compte</p>
    
    <?php if (isset($error) && $error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
            <div class="password-hint">Minimum 6 caractères</div>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit">Créer mon compte</button>
    </form>
    
    <div class="login-link">
        Déjà un compte ? <a href="login.php">Se connecter</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
