<?php
/**
 * Page de connexion (version MVC)
 */
session_start();

require_once 'controllers/AuthController.php';

$authController = new AuthController();
$error = '';

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $authController->login();
    
    if ($result['success']) {
        header('Location: app.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

// Afficher la vue
$authController->showLogin();
?>
