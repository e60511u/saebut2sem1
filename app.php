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
  <label for="site-search">Search the parking:</label>
<input type="search" id="site-search" name="q" />
<button>Search</button>
</div>
  <button id="stop-guidance" class="hidden">Stop guidance</button>
  <div id="map"></div>

  <script src="script.js"></script>
</body>
</html>
