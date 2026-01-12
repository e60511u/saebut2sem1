<?php
/**
 * Provider pour les parkings de Metz
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Cette classe a une seule responsabilité : adapter les données de Metz au format standardisé
 * 
 * Pattern Design : Adapter
 * Adapte le format GeoJSON/WFS de Metz au format standardisé de l'application
 */

require_once __DIR__ . '/ParkingProviderInterface.php';

class MetzParkingProvider implements ParkingProviderInterface {
    // URLs des APIs Open Data de Metz
    private const API_PARKING_TEMPS_REEL = 'https://maps.eurometropolemetz.eu/public/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=public:pub_tsp_sta&srsName=EPSG:4326&outputFormat=application%2Fjson&cql_filter=id%20is%20not%20null';
    private const API_PARKING_STATIQUE = 'https://maps.eurometropolemetz.eu/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=public:pub_acc_sta&srsName=EPSG:4326&outputFormat=json';
    
    /**
     * Récupère les données depuis une URL externe
     * 
     * @param string $url URL à appeler
     * @return array|null Données JSON décodées ou null en cas d'erreur
     */
    private function fetchData(string $url): ?array {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'method' => 'GET',
                'header' => 'User-Agent: SAE301-ParkingApp/1.0'
            ]
        ]);
        
        $json = @file_get_contents($url, false, $context);
        
        if ($json === false) {
            error_log("Erreur lors de la récupération des données depuis : " . $url);
            return null;
        }
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur de décodage JSON : " . json_last_error_msg());
            return null;
        }
        
        return $data;
    }
    
    /**
     * Nettoie et complète le nom d'un parking
     * 
     * @param array $properties Propriétés du parking
     * @return string Nom nettoyé
     */
    private function cleanParkingName(array $properties): string {
        $nom = $properties['lib'] ?? $properties['voie'] ?? 'Parking';
        
        // Nettoyer les noms tronqués
        $nom = trim($nom);
        if (mb_substr($nom, 0, 1) === '(' || mb_substr($nom, -1) === ')') {
            $voie = $properties['voie'] ?? '';
            if (!empty($voie) && $voie !== $nom) {
                $nom = $voie . ' ' . $nom;
            }
        }
        
        return trim($nom);
    }
    
    /**
     * Convertit une feature GeoJSON de Metz au format standardisé
     * 
     * @param array $feature Feature GeoJSON de Metz
     * @return array Parking au format standardisé
     */
    private function normalizeParking(array $feature): array {
        $properties = $feature['properties'] ?? [];
        $geometry = $feature['geometry'] ?? [];
        $coordinates = $geometry['coordinates'] ?? [];
        
        // Extraire les coordonnées (GeoJSON utilise [lng, lat])
        $lng = $coordinates[0] ?? null;
        $lat = $coordinates[1] ?? null;
        
        // Nom du parking
        $name = $this->cleanParkingName($properties);
        
        // ID unique
        $id = $properties['id'] ?? $feature['id'] ?? uniqid('metz_', true);
        
        // Places disponibles et totales
        // L'API Metz utilise différents noms selon la source (temps réel vs statique)
        // API temps réel (pub_tsp_sta) : utilise 'dispo' et 'total'
        // API statique (pub_acc_sta) : utilise 'place_libre' et 'place_total'
        $available = null;
        $total = null;
        
        // Essayer différents noms de propriétés possibles (ordre de priorité)
        if (isset($properties['place_libre']) && is_numeric($properties['place_libre'])) {
            $available = (int)$properties['place_libre'];
        } elseif (isset($properties['dispo']) && is_numeric($properties['dispo'])) {
            $available = (int)$properties['dispo'];
        }
        
        if (isset($properties['place_total']) && is_numeric($properties['place_total'])) {
            $total = (int)$properties['place_total'];
        } elseif (isset($properties['total']) && is_numeric($properties['total'])) {
            $total = (int)$properties['total'];
        }
        
        // Statut
        $status = 'UNKNOWN';
        if ($available !== null && $total !== null) {
            if ($available > 0) {
                $status = 'OPEN';
            } else {
                $status = 'CLOSED';
            }
        } elseif ($total !== null) {
            $status = 'OPEN'; // Si on a le total mais pas les disponibles, on assume ouvert
        }
        
        // Coût
        $cost = $properties['cout'] ?? null;
        
        // PMR (vérifier dans le nom ou les propriétés)
        $isPmr = false;
        $lib = mb_strtolower($properties['lib'] ?? '');
        if (mb_strpos($lib, 'accessible') !== false || 
            mb_strpos($lib, 'pmr') !== false ||
            mb_strpos($lib, 'handicap') !== false) {
            $isPmr = true;
        }
        
        // Bornes électriques (vérifier dans le nom)
        $hasElectric = false;
        if (mb_strpos($lib, 'électrique') !== false || 
            mb_strpos($lib, 'electrique') !== false ||
            mb_strpos($lib, 'recharge') !== false) {
            $hasElectric = true;
        }
        
        return [
            'id' => $id,
            'name' => $name,
            'lat' => $lat,
            'lng' => $lng,
            'total_places' => $total,
            'available_places' => $available,
            'status' => $status,
            'cost' => $cost,
            'is_pmr' => $isPmr,
            'has_electric_charging' => $hasElectric,
            'additional_info' => [
                'quartier' => $properties['quartier'] ?? null,
                'voie' => $properties['voie'] ?? null,
                'typ' => $properties['typ'] ?? null
            ]
        ];
    }
    
    /**
     * Récupère tous les parkings
     * 
     * @return array Liste de parkings au format standardisé
     */
    public function getAllParkings(): array {
        $parkingsTempsReel = $this->fetchData(self::API_PARKING_TEMPS_REEL);
        $parkingsStatiques = $this->fetchData(self::API_PARKING_STATIQUE);
        
        $parkings = [];
        
        // Traiter les parkings en temps réel
        if ($parkingsTempsReel && isset($parkingsTempsReel['features'])) {
            foreach ($parkingsTempsReel['features'] as $feature) {
                $normalized = $this->normalizeParking($feature);
                if ($normalized['lat'] !== null && $normalized['lng'] !== null) {
                    $parkings[] = $normalized;
                }
            }
        }
        
        // Traiter les parkings statiques
        if ($parkingsStatiques && isset($parkingsStatiques['features'])) {
            foreach ($parkingsStatiques['features'] as $feature) {
                $normalized = $this->normalizeParking($feature);
                if ($normalized['lat'] !== null && $normalized['lng'] !== null) {
                    // Éviter les doublons (vérifier par ID ou coordonnées)
                    $exists = false;
                    foreach ($parkings as $existing) {
                        if ($existing['id'] === $normalized['id'] || 
                            (abs($existing['lat'] - $normalized['lat']) < 0.0001 && 
                             abs($existing['lng'] - $normalized['lng']) < 0.0001)) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $parkings[] = $normalized;
                    }
                }
            }
        }
        
        return $parkings;
    }
    
    /**
     * Recherche des parkings par terme
     * 
     * @param string $query Terme de recherche
     * @return array Liste de parkings filtrés
     */
    public function searchParkings(string $query): array {
        $allParkings = $this->getAllParkings();
        $queryLower = mb_strtolower(trim($query));
        
        if (empty($queryLower)) {
            return [];
        }
        
        return array_filter($allParkings, function($parking) use ($queryLower) {
            $name = mb_strtolower($parking['name'] ?? '');
            $quartier = mb_strtolower($parking['additional_info']['quartier'] ?? '');
            
            return mb_strpos($name, $queryLower) !== false || 
                   mb_strpos($quartier, $queryLower) !== false;
        });
    }
    
    /**
     * Retourne le code de la ville
     * 
     * @return string Code de la ville
     */
    public function getCityCode(): string {
        return 'metz';
    }
}
