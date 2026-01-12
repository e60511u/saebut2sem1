<?php
/**
 * Script de test pour vérifier l'API Londres
 */

require_once __DIR__ . '/providers/LondonParkingProvider.php';

$apiKey = 'd0590429774e474f83f441ebd5a23c05';
$provider = new LondonParkingProvider($apiKey);

echo "Test de l'API Londres\n";
echo "====================\n\n";

try {
    $parkings = $provider->getAllParkings();
    
    echo "Nombre de parkings récupérés: " . count($parkings) . "\n\n";
    
    if (count($parkings) > 0) {
        echo "Premier parking:\n";
        print_r($parkings[0]);
    } else {
        echo "Aucun parking récupéré!\n";
    }
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
