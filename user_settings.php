<?php
session_start();
require_once 'db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = connectDB();

if ($pdo === null) {
    die('Erreur de connexion à la base de données. Veuillez vérifier votre configuration.');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Mise à jour des informations utilisateur
        if (isset($_POST['update_user'])) {
            $pseudo = $_POST['pseudo'] ?? '';
            $email = $_POST['email'] ?? '';
            $preference_cout = $_POST['preference_cout'] ?? 'INDIFFERENT';
            $est_pmr = isset($_POST['est_pmr']) ? 1 : 0;
            
            $stmt = $pdo->prepare("UPDATE Utilisateur SET pseudo = ?, email = ?, preference_cout = ?, est_pmr = ? WHERE id_utilisateur = ?");
            $stmt->execute([$pseudo, $email, $preference_cout, $est_pmr, $user_id]);
            
            $_SESSION['pseudo'] = $pseudo;
            $_SESSION['email'] = $email;
            $_SESSION['preference_cout'] = $preference_cout;
            $_SESSION['est_pmr'] = $est_pmr;
            $success = "Informations mises à jour avec succès";
        }
        
        // Ajout d'un véhicule
        if (isset($_POST['add_vehicle'])) {
            $nom = $_POST['vehicle_name'] ?? '';
            $type_id = $_POST['vehicle_type'] ?? 1;
            $moto_id = $_POST['motorisation'] ?? 1;
            
            $stmt = $pdo->prepare("INSERT INTO Vehicule (nom_vehicule, id_utilisateur, id_type_veh, id_motorisation) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $user_id, $type_id, $moto_id]);
            $success = "Véhicule ajouté avec succès";
        }
        
        // Suppression d'un véhicule
        if (isset($_POST['delete_vehicle'])) {
            $vehicle_id = $_POST['vehicle_id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM Vehicule WHERE id_vehicule = ? AND id_utilisateur = ?");
            $stmt->execute([$vehicle_id, $user_id]);
            $success = "Véhicule supprimé avec succès";
        }
        
        // Ajout d'un parking favori
        if (isset($_POST['add_favorite'])) {
            $parking_id = $_POST['parking_id'] ?? '';
            $nom_perso = $_POST['custom_name'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO Favori (id_utilisateur, ref_parking_api, nom_custom) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $parking_id, $nom_perso]);
            $success = "Parking favori ajouté avec succès";
        }
        
        // Suppression d'un parking favori
        if (isset($_POST['delete_favorite'])) {
            $favorite_id = $_POST['favorite_id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM Favori WHERE id_favori = ? AND id_utilisateur = ?");
            $stmt->execute([$favorite_id, $user_id]);
            $success = "Parking favori supprimé avec succès";
        }
        
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupération des données utilisateur
$stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $user = ['id_utilisateur' => $user_id, 'pseudo' => 'Utilisateur', 'email' => 'user@example.com', 'preference_cout' => 'INDIFFERENT', 'est_pmr' => 0];
}

// Récupération des types de véhicules et motorisations
$types_veh = $pdo->query("SELECT * FROM Ref_Type_Vehicule ORDER BY libelle_type")->fetchAll();
$motorisations = $pdo->query("SELECT * FROM Ref_Motorisation ORDER BY libelle_moto")->fetchAll();

// Récupération des véhicules avec leurs infos
$stmt = $pdo->prepare("
    SELECT v.*, t.libelle_type, m.libelle_moto 
    FROM Vehicule v
    JOIN Ref_Type_Vehicule t ON v.id_type_veh = t.id_type_veh
    JOIN Ref_Motorisation m ON v.id_motorisation = m.id_motorisation
    WHERE v.id_utilisateur = ?
");
$stmt->execute([$user_id]);
$vehicles = $stmt->fetchAll();

// Récupération des parkings favoris
$stmt = $pdo->prepare("SELECT * FROM Favori WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres utilisateur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #8A0808;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        h2 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 2px solid #8A0808;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        button {
            background: #8A0808;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        
        button:hover {
            background: #B71C1C;
        }
        
        button.delete {
            background: #666;
            padding: 5px 10px;
            font-size: 12px;
        }
        
        button.delete:hover {
            background: #888;
        }
        
        .item-list {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-info strong {
            color: #8A0808;
            display: block;
            margin-bottom: 5px;
        }
        
        .item-info span {
            color: #666;
            font-size: 14px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #8A0808;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="app.php" class="back-link">← Retour à la carte</a>
            <a href="logout.php" class="back-link" style="color: #666;">Déconnexion</a>
        </div>
        
        <h1>Paramètres utilisateur</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <h2>Informations personnelles</h2>
        <form method="POST">
            <div class="form-group">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="preference_cout">Préférence de coût</label>
                <select id="preference_cout" name="preference_cout">
                    <option value="INDIFFERENT" <?= $user['preference_cout'] == 'INDIFFERENT' ? 'selected' : '' ?>>Indifférent</option>
                    <option value="GRATUIT" <?= $user['preference_cout'] == 'GRATUIT' ? 'selected' : '' ?>>Gratuit uniquement</option>
                    <option value="PAYANT" <?= $user['preference_cout'] == 'PAYANT' ? 'selected' : '' ?>>Payant accepté</option>
                </select>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="est_pmr" <?= $user['est_pmr'] ? 'checked' : '' ?> style="width: auto;">
                    <span>Personne à mobilité réduite (PMR)</span>
                </label>
            </div>
            <button type="submit" name="update_user">Enregistrer</button>
        </form>
        
        <h2>Mes véhicules</h2>
        <div class="item-list">
            <?php if (empty($vehicles)): ?>
                <p style="color: #666;">Aucun véhicule enregistré</p>
            <?php else: ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="item">
                        <div class="item-info">
                            <strong><?= htmlspecialchars($vehicle['nom_vehicule']) ?></strong>
                            <span>Type: <?= htmlspecialchars($vehicle['libelle_type']) ?> | Motorisation: <?= htmlspecialchars($vehicle['libelle_moto']) ?></span>
                        </div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id_vehicule'] ?>">
                            <button type="submit" name="delete_vehicle" class="delete" onclick="return confirm('Supprimer ce véhicule ?')">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <h3 style="margin-bottom: 15px;">Ajouter un véhicule</h3>
            <div class="form-group">
                <label for="vehicle_name">Nom du véhicule</label>
                <input type="text" id="vehicle_name" name="vehicle_name" placeholder="Ex: Ma voiture" required>
            </div>
            <div class="form-group">
                <label for="vehicle_type">Type</label>
                <select id="vehicle_type" name="vehicle_type" required>
                    <?php foreach ($types_veh as $type): ?>
                        <option value="<?= $type['id_type_veh'] ?>"><?= htmlspecialchars($type['libelle_type']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="motorisation">Motorisation</label>
                <select id="motorisation" name="motorisation" required>
                    <?php foreach ($motorisations as $moto): ?>
                        <option value="<?= $moto['id_motorisation'] ?>"><?= htmlspecialchars($moto['libelle_moto']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_vehicle">Ajouter le véhicule</button>
        </form>
        
        <h2>Parkings favoris</h2>
        <div class="item-list">
            <?php if (empty($favorites)): ?>
                <p style="color: #666;">Aucun parking favori</p>
            <?php else: ?>
                <?php foreach ($favorites as $favorite): ?>
                    <div class="item">
                        <div class="item-info">
                            <strong><?= htmlspecialchars($favorite['nom_custom'] ?: $favorite['ref_parking_api']) ?></strong>
                            <span>Référence: <?= htmlspecialchars($favorite['ref_parking_api']) ?></span>
                        </div>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="favorite_id" value="<?= $favorite['id_favori'] ?>">
                            <button type="submit" name="delete_favorite" class="delete" onclick="return confirm('Supprimer ce favori ?')">Supprimer</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <h3 style="margin-bottom: 15px;">Ajouter un parking favori</h3>
            <div class="form-group">
                <label for="parking_id">ID du parking</label>
                <input type="text" id="parking_id" name="parking_id" placeholder="Ex: Parking Centre-Ville" required>
            </div>
            <div class="form-group">
                <label for="custom_name">Nom personnalisé</label>
                <input type="text" id="custom_name" name="custom_name" placeholder="Ex: Mon parking habituel" required>
            </div>
            <button type="submit" name="add_favorite">Ajouter aux favoris</button>
        </form>
    </div>
</body>
</html>
