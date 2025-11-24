<?php
/**
 * Contrôleur des parkings
 * Gère l'affichage de la carte et des parkings
 */
class ParkingController {
    
    public function __construct() {
        // Initialisation si nécessaire
    }
    
    /**
     * Afficher la carte des parkings
     */
    public function showMap() {
        require_once __DIR__ . '/../views/parking/map.php';
    }
}
?>
