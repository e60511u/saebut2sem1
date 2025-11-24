<?php
/**
 * Page des paramètres utilisateur (version MVC)
 */
session_start();

require_once 'controllers/AuthController.php';
require_once 'controllers/UserController.php';

$authController = new AuthController();
$userController = new UserController();

// Vérifier l'authentification
$authController->requireLogin();

$success = '';
$error = '';

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $result = $userController->updateProfile();
    } elseif (isset($_POST['add_vehicle'])) {
        $result = $userController->addVehicle();
    } elseif (isset($_POST['delete_vehicle'])) {
        $result = $userController->deleteVehicle();
    } elseif (isset($_POST['add_favorite'])) {
        $result = $userController->addFavorite();
    } elseif (isset($_POST['delete_favorite'])) {
        $result = $userController->deleteFavorite();
    }
    
    if (isset($result)) {
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Afficher la vue
$userController->showSettings();
?>
