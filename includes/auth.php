<?php
/**
 * Fichier de gestion de l'authentification
 */

/**
 * Connexion d'un utilisateur
 * @param string $identifier Email ou pseudo
 * @param string $password Mot de passe
 * @return array|false Retourne les données utilisateur ou false en cas d'échec
 */
function loginUser($identifier, $password) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    // Rechercher par email ou pseudo
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE email = ? OR pseudo = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['mot_de_passe'])) {
        return $user;
    }
    
    return false;
}

/**
 * Enregistrement d'un nouvel utilisateur
 * @param string $pseudo Pseudo de l'utilisateur
 * @param string $email Email de l'utilisateur
 * @param string $password Mot de passe en clair
 * @return array|string Retourne les données utilisateur ou un message d'erreur
 */
function registerUser($pseudo, $email, $password) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return 'Erreur de connexion à la base de données';
    }
    
    // Vérifier si l'email ou le pseudo existe déjà
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = ? OR pseudo = ?");
    $stmt->execute([$email, $pseudo]);
    
    if ($stmt->fetch()) {
        return 'Cet email ou pseudo est déjà utilisé';
    }
    
    // Créer le compte
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO Utilisateur (email, pseudo, mot_de_passe, est_pmr, preference_cout) VALUES (?, ?, ?, 0, 'INDIFFERENT')");
        $stmt->execute([$email, $pseudo, $hashed_password]);
        
        // Récupérer l'utilisateur créé
        $user_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$user_id]);
        
        return $stmt->fetch();
    } catch (PDOException $e) {
        return 'Erreur lors de la création du compte';
    }
}

/**
 * Initialiser la session utilisateur
 * @param array $user Données de l'utilisateur
 */
function initUserSession($user) {
    $_SESSION['user_id'] = $user['id_utilisateur'];
    $_SESSION['pseudo'] = $user['pseudo'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['preference_cout'] = $user['preference_cout'];
    $_SESSION['est_pmr'] = $user['est_pmr'];
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Rediriger vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
