<?php
/**
 * Déconnexion (version MVC)
 */
session_start();

require_once 'controllers/AuthController.php';

$authController = new AuthController();
$authController->logout();
?>
