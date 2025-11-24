<?php
/**
 * Modèle Favorite
 * Gère les parkings favoris des utilisateurs
 */
class Favorite {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Récupérer les favoris d'un utilisateur
     * @param int $userId
     * @return array
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM Favori WHERE id_utilisateur = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Ajouter un favori
     * @param int $userId
     * @param string $parkingId
     * @param string $customName
     * @return bool
     */
    public function create($userId, $parkingId, $customName) {
        try {
            $stmt = $this->db->prepare("INSERT INTO Favori (id_utilisateur, ref_parking_api, nom_custom) VALUES (?, ?, ?)");
            return $stmt->execute([$userId, $parkingId, $customName]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Supprimer un favori
     * @param int $favoriteId
     * @param int $userId
     * @return bool
     */
    public function delete($favoriteId, $userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM Favori WHERE id_favori = ? AND id_utilisateur = ?");
            return $stmt->execute([$favoriteId, $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
