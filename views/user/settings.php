<?php
$pageTitle = 'Paramètres utilisateur';
$additionalHead = '<link rel="stylesheet" href="assets/css/user_settings.css">';

ob_start();
?>
<div class="container">
    <div class="header-nav">
        <a href="app.php" class="back-link">← Retour à la carte</a>
        <a href="logout.php" class="back-link logout-link">Déconnexion</a>
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
            <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user->pseudo) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
        </div>
        <div class="form-group">
            <label for="preference_cout">Préférence de coût</label>
            <select id="preference_cout" name="preference_cout">
                <option value="INDIFFERENT" <?= $user->preference_cout == 'INDIFFERENT' ? 'selected' : '' ?>>Indifférent</option>
                <option value="GRATUIT" <?= $user->preference_cout == 'GRATUIT' ? 'selected' : '' ?>>Gratuit uniquement</option>
                <option value="PAYANT" <?= $user->preference_cout == 'PAYANT' ? 'selected' : '' ?>>Payant accepté</option>
            </select>
        </div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="est_pmr" <?= $user->est_pmr ? 'checked' : '' ?> style="width: auto;">
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
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
?>
