<?php
/**
 * Modèle Vehicle
 * Gère les véhicules des utilisateurs
 */
class Vehicle {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Récupérer les véhicules d'un utilisateur
     * @param int $userId
     * @return array
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT v.*, t.libelle_type, m.libelle_moto 
            FROM Vehicule v
            JOIN Ref_Type_Vehicule t ON v.id_type_veh = t.id_type_veh
            JOIN Ref_Motorisation m ON v.id_motorisation = m.id_motorisation
            WHERE v.id_utilisateur = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Ajouter un véhicule
     * @param int $userId
     * @param string $nom
     * @param int $typeId
     * @param int $motoisationId
     * @return bool
     */
    public function create($userId, $nom, $typeId, $motorisationId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO Vehicule (nom_vehicule, id_utilisateur, id_type_veh, id_motorisation) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$nom, $userId, $typeId, $motorisationId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Supprimer un véhicule
     * @param int $vehicleId
     * @param int $userId
     * @return bool
     */
    public function delete($vehicleId, $userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM Vehicule WHERE id_vehicule = ? AND id_utilisateur = ?");
            return $stmt->execute([$vehicleId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Récupérer tous les types de véhicules
     * @return array
     */
    public function getTypes() {
        return $this->db->query("SELECT * FROM Ref_Type_Vehicule ORDER BY libelle_type")->fetchAll();
    }
    
    /**
     * Récupérer toutes les motorisations
     * @return array
     */
    public function getMotorisations() {
        return $this->db->query("SELECT * FROM Ref_Motorisation ORDER BY libelle_moto")->fetchAll();
    }
}
?>
