<?php
/**
 * Modèle User
 * Représente un utilisateur et gère toutes les opérations liées
 */
class User {
    private $db;
    
    public $id;
    public $pseudo;
    public $email;
    public $mot_de_passe;
    public $preference_cout;
    public $est_pmr;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        if ($this->db === null) {
            throw new Exception("La connexion à la base de données n'est pas disponible");
        }
    }
    
    /**
     * Trouver un utilisateur par email ou pseudo
     * @param string $identifier Email ou pseudo
     * @return User|false
     */
    public function findByIdentifier($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE email = ? OR pseudo = ?");
        $stmt->execute([$identifier, $identifier]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->hydrate($data);
            return $this;
        }
        return false;
    }
    
    /**
     * Trouver un utilisateur par ID
     * @param int $id
     * @return User|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        if ($data) {
            $this->hydrate($data);
            return $this;
        }
        return false;
    }
    
    /**
     * Créer un nouvel utilisateur
     * @param string $pseudo
     * @param string $email
     * @param string $password
     * @return int|false ID de l'utilisateur créé ou false
     */
    public function create($pseudo, $email, $password) {
        // Vérifier si existe déjà
        $stmt = $this->db->prepare("SELECT id_utilisateur FROM Utilisateur WHERE email = ? OR pseudo = ?");
        $stmt->execute([$email, $pseudo]);
        
        if ($stmt->fetch()) {
            return false;
        }
        
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO Utilisateur (email, pseudo, mot_de_passe, est_pmr, preference_cout) VALUES (?, ?, ?, 0, 'INDIFFERENT')");
            $stmt->execute([$email, $pseudo, $hashed_password]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Mettre à jour un utilisateur
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE Utilisateur SET pseudo = ?, email = ?, preference_cout = ?, est_pmr = ? WHERE id_utilisateur = ?");
            return $stmt->execute([
                $data['pseudo'],
                $data['email'],
                $data['preference_cout'],
                $data['est_pmr'],
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Vérifier le mot de passe
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->mot_de_passe);
    }
    
    /**
     * Hydrater l'objet avec des données
     * @param array $data
     */
    private function hydrate($data) {
        $this->id = $data['id_utilisateur'];
        $this->pseudo = $data['pseudo'];
        $this->email = $data['email'];
        $this->mot_de_passe = $data['mot_de_passe'];
        $this->preference_cout = $data['preference_cout'];
        $this->est_pmr = $data['est_pmr'];
    }
    
    /**
     * Obtenir toutes les données sous forme de tableau
     * @return array
     */
    public function toArray() {
        return [
            'id_utilisateur' => $this->id,
            'pseudo' => $this->pseudo,
            'email' => $this->email,
            'preference_cout' => $this->preference_cout,
            'est_pmr' => $this->est_pmr
        ];
    }
}
?>
