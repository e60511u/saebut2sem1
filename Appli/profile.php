<?php
/**
 * Page de profil utilisateur
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Cette page permet à l'utilisateur de modifier son profil et ses véhicules
 */

require_once __DIR__ . '/config/Auth.php';
require_once __DIR__ . '/modele/User.php';

Auth::startSession();
Auth::requireLogin();

$userModel = new User();
$userId = Auth::getUserId();
$user = $userModel->getFullProfile($userId);
$vehicles = $user['vehicules'] ?? [];

$error = '';
$success = '';

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $estPmr = isset($_POST['est_pmr']) && $_POST['est_pmr'] === '1';
    $preferenceCout = $_POST['preference_cout'] ?? 'INDIFFERENT';
    
    if (empty($pseudo)) {
        $error = 'Le pseudo est obligatoire';
    } else {
        if ($userModel->updateProfile($userId, [
            'pseudo' => $pseudo,
            'est_pmr' => $estPmr,
            'preference_cout' => $preferenceCout
        ])) {
            $success = 'Profil mis à jour avec succès';
            // Recharger les données
            $user = $userModel->getFullProfile($userId);
        } else {
            $error = 'Erreur lors de la mise à jour';
        }
    }
}

// Traitement de l'ajout d'un véhicule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_vehicle') {
    $nomVehicule = trim($_POST['nom_vehicule'] ?? '');
    $idTypeVeh = (int)($_POST['id_type_veh'] ?? 0);
    $idMotorisation = (int)($_POST['id_motorisation'] ?? 0);
    
    if (empty($nomVehicule) || $idTypeVeh === 0 || $idMotorisation === 0) {
        $error = 'Tous les champs du véhicule sont obligatoires';
    } else {
        if ($userModel->addVehicle($userId, $nomVehicule, $idTypeVeh, $idMotorisation)) {
            $success = 'Véhicule ajouté avec succès';
            // Recharger les données
            $user = $userModel->getFullProfile($userId);
            $vehicles = $user['vehicules'] ?? [];
        } else {
            $error = 'Erreur lors de l\'ajout du véhicule';
        }
    }
}

// Récupérer les types de véhicules et motorisations
$db = Database::getInstance();
$typesVehicules = $db->query("SELECT * FROM Ref_Type_Vehicule ORDER BY id_type_veh")->fetchAll();
$motorisations = $db->query("SELECT * FROM Ref_Motorisation ORDER BY id_motorisation")->fetchAll();

$pageTitle = 'Mon Profil - Parking Metz';
require_once __DIR__ . '/includes/header.php';
?>

<div class="profile-container">
    <div class="profile-box">
        <h1>Mon Profil</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Formulaire de mise à jour du profil -->
        <section class="profile-section">
            <h2>Informations personnelles</h2>
            <form method="POST" action="profile.php" class="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input 
                        type="text" 
                        id="pseudo" 
                        name="pseudo" 
                        required 
                        value="<?php echo htmlspecialchars($user['pseudo'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                        disabled
                    >
                    <small>L'email ne peut pas être modifié</small>
                </div>
                
                <div class="form-group">
                    <label for="preference_cout">Préférence de coût</label>
                    <select id="preference_cout" name="preference_cout">
                        <option value="INDIFFERENT" <?php echo ($user['preference_cout'] ?? 'INDIFFERENT') === 'INDIFFERENT' ? 'selected' : ''; ?>>Peu importe</option>
                        <option value="GRATUIT" <?php echo ($user['preference_cout'] ?? '') === 'GRATUIT' ? 'selected' : ''; ?>>Gratuit uniquement</option>
                        <option value="PAYANT" <?php echo ($user['preference_cout'] ?? '') === 'PAYANT' ? 'selected' : ''; ?>>Payant uniquement</option>
                    </select>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input 
                            type="checkbox" 
                            name="est_pmr" 
                            value="1"
                            <?php echo ($user['est_pmr'] ?? 0) ? 'checked' : ''; ?>
                        >
                        <span>Situation de handicap (PMR)</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-primary">Mettre à jour</button>
            </form>
        </section>
        
        <!-- Gestion des véhicules -->
        <section class="profile-section">
            <h2>Mes véhicules</h2>
            
            <?php if (!empty($vehicles)): ?>
                <div class="vehicles-list">
                    <?php foreach ($vehicles as $vehicle): ?>
                        <div class="vehicle-item">
                            <strong><?php echo htmlspecialchars($vehicle['nom_vehicule'] ?? 'Véhicule'); ?></strong>
                            <span class="vehicle-info">
                                <?php echo htmlspecialchars($vehicle['libelle_type'] ?? ''); ?> - 
                                <?php echo htmlspecialchars($vehicle['libelle_moto'] ?? ''); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3>Ajouter un véhicule</h3>
            <form method="POST" action="profile.php" class="profile-form">
                <input type="hidden" name="action" value="add_vehicle">
                
                <div class="form-group">
                    <label for="nom_vehicule">Nom du véhicule</label>
                    <input 
                        type="text" 
                        id="nom_vehicule" 
                        name="nom_vehicule" 
                        placeholder="Ex: Ma Clio, Mon VTT..."
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="id_type_veh">Type de véhicule</label>
                    <select id="id_type_veh" name="id_type_veh" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($typesVehicules as $type): ?>
                            <option value="<?php echo $type['id_type_veh']; ?>">
                                <?php echo htmlspecialchars($type['libelle_type']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_motorisation">Motorisation</label>
                    <select id="id_motorisation" name="id_motorisation" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($motorisations as $moto): ?>
                            <option value="<?php echo $moto['id_motorisation']; ?>">
                                <?php echo htmlspecialchars($moto['libelle_moto']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">Ajouter</button>
            </form>
        </section>
        
        <div class="profile-actions">
            <a href="index.php" class="btn-secondary">Retour à la carte</a>
            <a href="logout.php" class="btn-danger">Se déconnecter</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
