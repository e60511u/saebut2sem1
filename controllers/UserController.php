<?php
/**
 * Contrôleur utilisateur
 * Gère le profil, les véhicules et les favoris
 */
class UserController {
    private $userModel;
    private $vehicleModel;
    private $favoriteModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Database.php';
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Vehicle.php';
        require_once __DIR__ . '/../models/Favorite.php';
        
        $this->userModel = new User();
        $this->vehicleModel = new Vehicle();
        $this->favoriteModel = new Favorite();
    }
    
    /**
     * Afficher les paramètres utilisateur
     */
    public function showSettings() {
        $userId = $_SESSION['user_id'];
        
        // Récupérer les données
        $user = $this->userModel->findById($userId);
        if (!$user) {
            $user = (object)['id' => $userId, 'pseudo' => 'Utilisateur', 'email' => 'user@example.com', 'preference_cout' => 'INDIFFERENT', 'est_pmr' => 0];
        }
        
        $vehicles = $this->vehicleModel->findByUserId($userId);
        $favorites = $this->favoriteModel->findByUserId($userId);
        $types_veh = $this->vehicleModel->getTypes();
        $motorisations = $this->vehicleModel->getMotorisations();
        
        require_once __DIR__ . '/../views/user/settings.php';
    }
    
    /**
     * Mettre à jour le profil
     */
    public function updateProfile() {
        $userId = $_SESSION['user_id'];
        
        $data = [
            'pseudo' => $_POST['pseudo'] ?? '',
            'email' => $_POST['email'] ?? '',
            'preference_cout' => $_POST['preference_cout'] ?? 'INDIFFERENT',
            'est_pmr' => isset($_POST['est_pmr']) ? 1 : 0
        ];
        
        if ($this->userModel->update($userId, $data)) {
            // Mettre à jour la session
            $_SESSION['pseudo'] = $data['pseudo'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['preference_cout'] = $data['preference_cout'];
            $_SESSION['est_pmr'] = $data['est_pmr'];
            
            return ['success' => true, 'message' => 'Informations mises à jour avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
    }
    
    /**
     * Ajouter un véhicule
     */
    public function addVehicle() {
        $userId = $_SESSION['user_id'];
        $nom = $_POST['vehicle_name'] ?? '';
        $typeId = $_POST['vehicle_type'] ?? 1;
        $motoId = $_POST['motorisation'] ?? 1;
        
        if ($this->vehicleModel->create($userId, $nom, $typeId, $motoId)) {
            return ['success' => true, 'message' => 'Véhicule ajouté avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de l\'ajout du véhicule'];
    }
    
    /**
     * Supprimer un véhicule
     */
    public function deleteVehicle() {
        $userId = $_SESSION['user_id'];
        $vehicleId = $_POST['vehicle_id'] ?? 0;
        
        if ($this->vehicleModel->delete($vehicleId, $userId)) {
            return ['success' => true, 'message' => 'Véhicule supprimé avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la suppression du véhicule'];
    }
    
    /**
     * Ajouter un favori
     */
    public function addFavorite() {
        $userId = $_SESSION['user_id'];
        $parkingId = $_POST['parking_id'] ?? '';
        $customName = $_POST['custom_name'] ?? '';
        
        if ($this->favoriteModel->create($userId, $parkingId, $customName)) {
            return ['success' => true, 'message' => 'Parking favori ajouté avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de l\'ajout du favori'];
    }
    
    /**
     * Supprimer un favori
     */
    public function deleteFavorite() {
        $userId = $_SESSION['user_id'];
        $favoriteId = $_POST['favorite_id'] ?? 0;
        
        if ($this->favoriteModel->delete($favoriteId, $userId)) {
            return ['success' => true, 'message' => 'Parking favori supprimé avec succès'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la suppression du favori'];
    }
}
?>
