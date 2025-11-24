<?php
// Point d'entrée
require_once __DIR__ . '/controleur/ParkingController.php';

// Routage
$action = $_GET['action'] ?? 'index';

$controller = new ParkingController();

switch ($action) {
    case 'index':
        $controller->index();
        break;
        
    case 'getParkings':
        $controller->getParkingsJson();
        break;
        
    case 'searchParkings':
        $controller->searchParkingsJson();
        break;
        
    default:
        $controller->index();
        break;
}
