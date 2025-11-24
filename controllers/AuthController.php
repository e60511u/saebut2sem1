<?php
/**
 * Contrôleur d'authentification
 * Gère la connexion, l'inscription et la déconnexion
 */
class AuthController {
    private $userModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/User.php';
        $this->userModel = new User();
    }
    
    /**
     * Afficher la page de connexion
     */
    public function showLogin() {
        // Rediriger si déjà connecté
        if ($this->isLoggedIn()) {
            header('Location: app.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    /**
     * Traiter la connexion
     */
    public function login() {
        $identifier = $_POST['identifier'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($identifier) || empty($password)) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs'];
        }
        
        $user = $this->userModel->findByIdentifier($identifier);
        
        if ($user && $user->verifyPassword($password)) {
            $this->initSession($user);
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Identifiant ou mot de passe incorrect'];
    }
    
    /**
     * Afficher la page d'inscription
     */
    public function showRegister() {
        // Rediriger si déjà connecté
        if ($this->isLoggedIn()) {
            header('Location: app.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    /**
     * Traiter l'inscription
     */
    public function register() {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($pseudo) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Veuillez remplir tous les champs'];
        }
        
        if ($password !== $confirm_password) {
            return ['success' => false, 'message' => 'Les mots de passe ne correspondent pas'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }
        
        $userId = $this->userModel->create($pseudo, $email, $password);
        
        if ($userId) {
            $user = $this->userModel->findById($userId);
            $this->initSession($user);
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Cet email ou pseudo est déjà utilisé'];
    }
    
    /**
     * Déconnexion
     */
    public function logout() {
        session_start();
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    /**
     * Initialiser la session utilisateur
     * @param User $user
     */
    private function initSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['pseudo'] = $user->pseudo;
        $_SESSION['email'] = $user->email;
        $_SESSION['preference_cout'] = $user->preference_cout;
        $_SESSION['est_pmr'] = $user->est_pmr;
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Protéger une page (rediriger si non connecté)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
}
?>
