<?php
/**
 * Fichier de gestion des utilisateurs et de leurs données
 */

/**
 * Récupérer les informations d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @return array|false Données de l'utilisateur ou false
 */
function getUserById($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetch();
}

/**
 * Mettre à jour les informations d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @param string $pseudo Pseudo
 * @param string $email Email
 * @param string $preference_cout Préférence de coût
 * @param int $est_pmr Personne à mobilité réduite (0 ou 1)
 * @return bool Succès de la mise à jour
 */
function updateUser($user_id, $pseudo, $email, $preference_cout, $est_pmr) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE Utilisateur SET pseudo = ?, email = ?, preference_cout = ?, est_pmr = ? WHERE id_utilisateur = ?");
        $stmt->execute([$pseudo, $email, $preference_cout, $est_pmr, $user_id]);
        
        // Mettre à jour la session
        $_SESSION['pseudo'] = $pseudo;
        $_SESSION['email'] = $email;
        $_SESSION['preference_cout'] = $preference_cout;
        $_SESSION['est_pmr'] = $est_pmr;
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupérer les véhicules d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des véhicules
 */
function getUserVehicles($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return [];
    }
    
    $stmt = $pdo->prepare("
        SELECT v.*, t.libelle_type, m.libelle_moto 
        FROM Vehicule v
        JOIN Ref_Type_Vehicule t ON v.id_type_veh = t.id_type_veh
        JOIN Ref_Motorisation m ON v.id_motorisation = m.id_motorisation
        WHERE v.id_utilisateur = ?
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

/**
 * Ajouter un véhicule
 * @param int $user_id ID de l'utilisateur
 * @param string $nom Nom du véhicule
 * @param int $type_id ID du type de véhicule
 * @param int $moto_id ID de la motorisation
 * @return bool Succès de l'ajout
 */
function addVehicle($user_id, $nom, $type_id, $moto_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO Vehicule (nom_vehicule, id_utilisateur, id_type_veh, id_motorisation) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $user_id, $type_id, $moto_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Supprimer un véhicule
 * @param int $user_id ID de l'utilisateur
 * @param int $vehicle_id ID du véhicule
 * @return bool Succès de la suppression
 */
function deleteVehicle($user_id, $vehicle_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM Vehicule WHERE id_vehicule = ? AND id_utilisateur = ?");
        $stmt->execute([$vehicle_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupérer les parkings favoris d'un utilisateur
 * @param int $user_id ID de l'utilisateur
 * @return array Liste des favoris
 */
function getUserFavorites($user_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return [];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM Favori WHERE id_utilisateur = ?");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}

/**
 * Ajouter un parking favori
 * @param int $user_id ID de l'utilisateur
 * @param string $parking_id ID du parking
 * @param string $nom_perso Nom personnalisé
 * @return bool Succès de l'ajout
 */
function addFavorite($user_id, $parking_id, $nom_perso) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO Favori (id_utilisateur, ref_parking_api, nom_custom) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $parking_id, $nom_perso]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Supprimer un parking favori
 * @param int $user_id ID de l'utilisateur
 * @param int $favorite_id ID du favori
 * @return bool Succès de la suppression
 */
function deleteFavorite($user_id, $favorite_id) {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM Favori WHERE id_favori = ? AND id_utilisateur = ?");
        $stmt->execute([$favorite_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupérer les types de véhicules
 * @return array Liste des types de véhicules
 */
function getVehicleTypes() {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return [];
    }
    
    return $pdo->query("SELECT * FROM Ref_Type_Vehicule ORDER BY libelle_type")->fetchAll();
}

/**
 * Récupérer les motorisations
 * @return array Liste des motorisations
 */
function getMotorisations() {
    require_once __DIR__ . '/../config/db.php';
    $pdo = connectDB();
    
    if ($pdo === null) {
        return [];
    }
    
    return $pdo->query("SELECT * FROM Ref_Motorisation ORDER BY libelle_moto")->fetchAll();
}
?>
