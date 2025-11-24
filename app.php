<?php
/**
 * Page de la carte (version MVC)
 */
session_start();

require_once 'controllers/ParkingController.php';

$parkingController = new ParkingController();
$parkingController->showMap();
?>
