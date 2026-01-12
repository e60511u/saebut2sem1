<?php
/**
 * Modèle User - Gestion des utilisateurs
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Cette classe a une seule responsabilité : gérer toutes les interactions BDD liées aux utilisateurs
 * 
 * Principe : Separation of Concerns - Toute la logique d'accès aux données utilisateur est isolée ici
 */

require_once __DIR__ . '/../config/db.php';

class User {
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Crée un nouvel utilisateur
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair (sera haché)
     * @param string $pseudo Pseudo de l'utilisateur
     * @param bool $estPmr Statut PMR
     * @param string $preferenceCout Préférence de coût
     * @return int|false ID de l'utilisateur créé ou false en cas d'erreur
     */
    public function create(string $email, string $password, string $pseudo, bool $estPmr = false, string $preferenceCout = 'INDIFFERENT'): int|false {
        // Vérifier que l'email n'existe pas déjà
        if ($this->emailExists($email)) {
            return false;
        }
        
        // Hacher le mot de passe
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Utilisateur (email, mot_de_passe, pseudo, est_pmr, preference_cout)
                VALUES (:email, :password, :pseudo, :est_pmr, :preference_cout)
            ");
            
            $stmt->execute([
                'email' => $email,
                'password' => $passwordHash,
                'pseudo' => $pseudo,
                'est_pmr' => $estPmr ? 1 : 0,
                'preference_cout' => $preferenceCout
            ]);
            
            return (int)$this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un email existe déjà
     * 
     * @param string $email Email à vérifier
     * @return bool True si l'email existe
     */
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Vérifie les identifiants de connexion
     * 
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return array|false Données de l'utilisateur ou false si échec
     */
    public function authenticate(string $email, string $password): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT id_utilisateur, email, mot_de_passe, pseudo, est_pmr, preference_cout
                FROM Utilisateur
                WHERE email = :email
            ");
            
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $user['mot_de_passe'])) {
                return false;
            }
            
            // Mettre à jour la date de dernière connexion si la colonne existe
            try {
                $updateStmt = $this->db->prepare("
                    UPDATE Utilisateur 
                    SET derniere_connexion = CURRENT_TIMESTAMP 
                    WHERE id_utilisateur = :id
                ");
                $updateStmt->execute(['id' => $user['id_utilisateur']]);
            } catch (PDOException $e) {
                // La colonne n'existe peut-être pas encore, on ignore l'erreur
            }
            
            // Retourner les données sans le mot de passe
            unset($user['mot_de_passe']);
            return $user;
            
        } catch (PDOException $e) {
            error_log("Erreur lors de l'authentification : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un utilisateur par son ID
     * 
     * @param int $userId ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getById(int $userId): array|false {
        try {
            $stmt = $this->db->prepare("
                SELECT id_utilisateur, email, pseudo, est_pmr, preference_cout, date_creation
                FROM Utilisateur
                WHERE id_utilisateur = :id
            ");
            
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch() ?: false;
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour le profil d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool True si succès
     */
    public function updateProfile(int $userId, array $data): bool {
        $allowedFields = ['pseudo', 'est_pmr', 'preference_cout'];
        $updates = [];
        $params = ['id' => $userId];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "{$field} = :{$field}";
                $params[$field] = $field === 'est_pmr' ? ($value ? 1 : 0) : $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        try {
            $sql = "UPDATE Utilisateur SET " . implode(', ', $updates) . " WHERE id_utilisateur = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du profil : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les véhicules d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des véhicules
     */
    public function getVehicles(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    v.id_vehicule,
                    v.nom_vehicule,
                    v.id_type_veh,
                    v.id_motorisation,
                    tv.libelle_type,
                    m.libelle_moto
                FROM Vehicule v
                LEFT JOIN Ref_Type_Vehicule tv ON tv.id_type_veh = v.id_type_veh
                LEFT JOIN Ref_Motorisation m ON m.id_motorisation = v.id_motorisation
                WHERE v.id_utilisateur = :id
            ");
            
            $stmt->execute(['id' => $userId]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des véhicules : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ajoute un véhicule à un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $nomVehicule Nom du véhicule
     * @param int $idTypeVeh ID du type de véhicule
     * @param int $idMotorisation ID de la motorisation
     * @return int|false ID du véhicule créé ou false
     */
    public function addVehicle(int $userId, string $nomVehicule, int $idTypeVeh, int $idMotorisation): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO Vehicule (nom_vehicule, id_utilisateur, id_type_veh, id_motorisation)
                VALUES (:nom, :user_id, :type_veh, :motorisation)
            ");
            
            $stmt->execute([
                'nom' => $nomVehicule,
                'user_id' => $userId,
                'type_veh' => $idTypeVeh,
                'motorisation' => $idMotorisation
            ]);
            
            return (int)$this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout du véhicule : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère le profil complet d'un utilisateur (avec véhicules)
     * 
     * @param int $userId ID de l'utilisateur
     * @return array|false Profil complet ou false
     */
    public function getFullProfile(int $userId): array|false {
        $user = $this->getById($userId);
        
        if (!$user) {
            return false;
        }
        
        $user['vehicules'] = $this->getVehicles($userId);
        return $user;
    }
}
