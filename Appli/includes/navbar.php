<?php
/**
 * Barre de navigation
 * 
 * Principe : Separation of Concerns
 * Ce fichier contient uniquement le HTML de la navigation
 */

require_once __DIR__ . '/../config/Auth.php';
Auth::startSession();
$isLoggedIn = Auth::isLoggedIn();
$user = Auth::getUser();
?>
<nav id="main-navbar" class="navbar">
    <div class="navbar-brand">
        <h1>Parking Metz</h1>
    </div>
    <div class="navbar-menu">
        <select id="city-selector" class="city-selector">
            <option value="metz">Metz</option>
            <option value="london">Londres</option>
        </select>
        
        <?php if ($isLoggedIn): ?>
            <a href="profile.php" class="navbar-link">
                <span class="navbar-icon">👤</span>
                <?php echo htmlspecialchars($user['pseudo'] ?? 'Mon Profil'); ?>
            </a>
            <a href="logout.php" class="navbar-link navbar-link-logout">
                Déconnexion
            </a>
        <?php else: ?>
            <a href="login.php" class="navbar-link">
                Connexion
            </a>
            <a href="register.php" class="navbar-link navbar-link-register">
                Inscription
            </a>
        <?php endif; ?>
    </div>
</nav>
