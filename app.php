<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Carte SAE</title>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  
  <link rel="stylesheet" href="style.css"/>
</head>
<body>
  <div id ="search-bar">
  <label for="site-search">Chercher parking:</label>
<input type="search" id="site-search" name="q" placeholder="Chercher parking..." autocomplete="off" />
<div id="suggestions" class="suggestions hidden"></div>
<button id="search-button">Chercher</button>
</div>
  <div id="map"></div>
  <div id="follow-switch-container" class="hidden">
    <label class="switch-label">
      <input type="checkbox" id="follow-user" checked>
      <span class="switch-slider"></span>
      <span class="switch-text">Suivre ma position</span>
    </label>
  </div>
  <button id="nearest-parking">Au parking le plus proche</button>
  <button id="stop-guidance" class="hidden">Arrêter la navigation</button>
  
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="user_settings.php" id="user-button" title="Paramètres utilisateur">
      ⚙️
    </a>
  <?php else: ?>
    <a href="login.php" id="user-button" title="Se connecter">
      👤
    </a>
  <?php endif; ?>

  <script src="script.js"></script>
</body>
</html>
