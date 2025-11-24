<?php
class ParkingModel {
    
    // URLs des APIs
    private const API_PARKING_TEMPS_REEL = 'https://maps.eurometropolemetz.eu/public/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=public:pub_tsp_sta&srsName=EPSG:4326&outputFormat=json';
    private const API_PARKING_SUPPLEMENTAIRES = 'https://maps.eurometropolemetz.eu/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=public:pub_acc_sta&srsName=EPSG:4326&outputFormat=json';
    
    // Récupérer parkings temps réel
    public function getParkingsTempsReel() {
        try {
            $json = @file_get_contents(self::API_PARKING_TEMPS_REEL);
            if ($json === false) {
                return null;
            }
            return json_decode($json, true);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des parkings temps réel: " . $e->getMessage());
            return null;
        }
    }
    
    // Récupérer places supplémentaires
    public function getParkingsSupplementaires() {
        try {
            $json = @file_get_contents(self::API_PARKING_SUPPLEMENTAIRES);
            if ($json === false) {
                return null;
            }
            return json_decode($json, true);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des parkings supplémentaires: " . $e->getMessage());
            return null;
        }
    }
    
    // Récupérer tous les parkings
    public function getAllParkings() {
        $parkingsTempsReel = $this->getParkingsTempsReel();
        $parkingsSupplementaires = $this->getParkingsSupplementaires();
        
        $result = [
            'type' => 'FeatureCollection',
            'features' => []
        ];
        
        if ($parkingsTempsReel && isset($parkingsTempsReel['features'])) {
            $result['features'] = array_merge($result['features'], $parkingsTempsReel['features']);
        }
        
        if ($parkingsSupplementaires && isset($parkingsSupplementaires['features'])) {
            $result['features'] = array_merge($result['features'], $parkingsSupplementaires['features']);
        }
        
        return $result;
    }
    
    // Rechercher parkings par nom ou quartier
    public function searchParkings($query) {
        $allParkings = $this->getAllParkings();
        $query = strtolower($query);
        
        $results = array_filter($allParkings['features'], function($feature) use ($query) {
            $nom = isset($feature['properties']['lib']) ? strtolower($feature['properties']['lib']) : '';
            $voie = isset($feature['properties']['voie']) ? strtolower($feature['properties']['voie']) : '';
            $quartier = isset($feature['properties']['quartier']) ? strtolower($feature['properties']['quartier']) : '';
            
            return strpos($nom, $query) !== false || 
                   strpos($voie, $query) !== false || 
                   strpos($quartier, $query) !== false;
        });
        
        return [
            'type' => 'FeatureCollection',
            'features' => array_values($results)
        ];
    }
}
