<?php
require_once __DIR__ . '/../modele/ParkingModel.php';

class ParkingController {
    
    private $model;
    
    public function __construct() {
        $this->model = new ParkingModel();
    }
    
    // Afficher la page
    public function index() {
        $pageTitle = 'SAE Parking - Carte';
        require_once __DIR__ . '/../vue/parking.php';
    }
    
    // Retourner les parkings en JSON
    public function getParkingsJson() {
        header('Content-Type: application/json');
        $parkings = $this->model->getAllParkings();
        echo json_encode($parkings);
        exit;
    }
    
    // Rechercher des parkings
    public function searchParkingsJson() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['q']) || empty($_GET['q'])) {
            echo json_encode(['type' => 'FeatureCollection', 'features' => []]);
            exit;
        }
        
        $query = $_GET['q'];
        $results = $this->model->searchParkings($query);
        echo json_encode($results);
        exit;
    }
}
