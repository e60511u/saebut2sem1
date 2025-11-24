<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'SAE Parking - Carte'; ?></title>
  
  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  
  <link rel="stylesheet" href="vue/assets/css/style.css"/>
</head>
<body>
  <div id="search-bar">
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

  <script src="vue/assets/js/script.js"></script>
</body>
</html>
