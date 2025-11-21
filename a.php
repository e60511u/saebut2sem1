<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Carte SAE</title>

  <!-- Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  
  <!-- Style -->
  <link rel="stylesheet" href="style.css"/>
</head>
<body>
  <div id ="search-bar">
  <label for="site-search">Search the parking:</label>
<input type="search" id="site-search" name="q" />
<button>Search</button>
<button id="stop-guidance">Stopper le guidage</button>
</div>
  <div id="map"></div>

  <script>
    const API_KEY = "jJUsi1FtC25BN9vwTwaKuc7Q80JTVPHh";

    const map = L.map('map').setView([0, 0], 2);

    const tomtomTiles = `https://api.tomtom.com/map/1/tile/basic/main/{z}/{x}/{y}.png?view=Unified&key=${API_KEY}`;

    L.tileLayer(tomtomTiles, {
      maxZoom: 20,
      attribution: '&copy; TomTom'
    }).addTo(map);

    const userIcon = L.icon({
      iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
      shadowUrl: '',
      iconSize: [25, 41],
      iconAnchor: [12, 41]
    });

    const destinationIcon = L.icon({
      iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
      shadowUrl: '',
      iconSize: [25, 41],
      iconAnchor: [12, 41]
    });

    let userMarker = null;
    let userPosition = null;
    let destinationMarker = null;
    let routeLayer = null;
    let parkingMarkers = [];
    let isGuiding = false;
    let parkingData = null;
    let secondRouteLayer = null;

    // Géolocalisation
    function updateUserPosition() {
      navigator.geolocation.getCurrentPosition((position) => {
        userPosition = [position.coords.latitude, position.coords.longitude];
        
        if (userMarker) {
          userMarker.setLatLng(userPosition);
        } else {
          userMarker = L.marker(userPosition, { icon: userIcon }).addTo(map);
          map.setView(userPosition, 15);
        }
      });
    }

    updateUserPosition();
    setInterval(updateUserPosition, 5000);

    // Calculer l'itinéraire
    async function calculateRoute(start, end) {
      if (routeLayer) map.removeLayer(routeLayer);

      const url = `https://api.tomtom.com/routing/1/calculateRoute/${start[0]},${start[1]}:${end[0]},${end[1]}/json?key=${API_KEY}`;
      const response = await fetch(url);
      const data = await response.json();
      
      const coordinates = data.routes[0].legs[0].points.map(p => [p.latitude, p.longitude]);
      
      routeLayer = L.polyline(coordinates, {
        color: '#8A0808',
        weight: 5,
        opacity: 0.7
      }).addTo(map);

      map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
    }

    // Récupérer et afficher les parkings
    async function loadParkings() {
      try {
        const response = await fetch('https://maps.eurometropolemetz.eu/public/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=public:pub_tsp_sta&srsName=EPSG:4326&outputFormat=application%2Fjson&cql_filter=id%20is%20not%20null');
        const data = await response.json();
        
        parkingData = data;
        
        parkingMarkers.forEach(marker => map.removeLayer(marker));
        parkingMarkers = [];
        
        data.features.forEach(feature => {
          const coords = feature.geometry.coordinates;
          const name = feature.properties.lib || 'Parking';
          const disponibles = feature.properties.place_libre || 0;
          const total = feature.properties.place_total || 0;
          
          const parkingIcon = L.divIcon({
            html: `<div style="background: #8A0808; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
            className: '',
            iconSize: [12, 12],
            iconAnchor: [6, 6]
          });
          
          const popupContent = `
            <div>
              <b>${name}</b><br>
              Disponibles: ${disponibles}/${total}<br>
              <button onclick="startGuidance(${coords[1]}, ${coords[0]})" style="margin-top: 10px; padding: 8px 16px; background: #8A0808; color: white; border: none; border-radius: 5px; cursor: pointer;">M'y guider</button>
            </div>
          `;
          
          const marker = L.marker([coords[1], coords[0]], { icon: parkingIcon })
            .bindPopup(popupContent)
            .addTo(map);
          
          parkingMarkers.push(marker);
        });
      } catch (error) {
        console.error('Erreur lors du chargement des parkings:', error);
      }
    }

    loadParkings();
    setInterval(loadParkings, 30000);

    // Démarrer le guidage vers un parking
    window.startGuidance = function(lat, lng) {
      if (!userPosition) return;
      
      isGuiding = true;
      
      // Cacher tous les parkings
      parkingMarkers.forEach(marker => map.removeLayer(marker));
      
      // Supprimer le marqueur de destination précédent
      if (destinationMarker) map.removeLayer(destinationMarker);
      if (secondRouteLayer) map.removeLayer(secondRouteLayer);
      
      // Ajouter le marqueur de destination
      destinationMarker = L.marker([lat, lng], { icon: destinationIcon }).addTo(map);
      
      // Calculer l'itinéraire
      calculateRoute(userPosition, [lat, lng]);
      
      // Afficher le bouton stopper
      document.getElementById('stop-guidance').style.display = 'block';
      
      // Fermer le popup
      map.closePopup();
    };

    // Stopper le guidage
    document.getElementById('stop-guidance').addEventListener('click', function() {
      isGuiding = false;
      
      // Supprimer l'itinéraire
      if (routeLayer) map.removeLayer(routeLayer);
      if (secondRouteLayer) map.removeLayer(secondRouteLayer);
      
      // Supprimer le marqueur de destination
      if (destinationMarker) map.removeLayer(destinationMarker);
      
      // Réafficher les parkings
      loadParkings();
      
      // Cacher le bouton stopper
      this.style.display = 'none';
    });

    // Trouver le parking le plus proche
    function findNearestParking(lat, lng) {
      if (!parkingData || !parkingData.features) return null;
      
      let nearest = null;
      let minDistance = Infinity;
      
      parkingData.features.forEach(feature => {
        const coords = feature.geometry.coordinates;
        const parkingLat = coords[1];
        const parkingLng = coords[0];
        
        const distance = Math.sqrt(
          Math.pow(lat - parkingLat, 2) + Math.pow(lng - parkingLng, 2)
        );
        
        if (distance < minDistance) {
          minDistance = distance;
          nearest = { lat: parkingLat, lng: parkingLng };
        }
      });
      
      return nearest;
    }

    // Calculer le deuxième itinéraire (du parking à la destination)
    async function calculateSecondRoute(start, end) {
      if (secondRouteLayer) map.removeLayer(secondRouteLayer);

      const url = `https://api.tomtom.com/routing/1/calculateRoute/${start[0]},${start[1]}:${end[0]},${end[1]}/json?key=${API_KEY}`;
      const response = await fetch(url);
      const data = await response.json();
      
      const coordinates = data.routes[0].legs[0].points.map(p => [p.latitude, p.longitude]);
      
      secondRouteLayer = L.polyline(coordinates, {
        color: '#808080',
        weight: 5,
        opacity: 0.7
      }).addTo(map);
    }

    // Clic sur la carte
    map.on('click', async function(e) {
      if (isGuiding) return;
      if (!userPosition) return;
      
      const clickLat = e.latlng.lat;
      const clickLng = e.latlng.lng;
      
      // Trouver le parking le plus proche
      const nearestParking = findNearestParking(clickLat, clickLng);
      
      if (!nearestParking) return;
      
      isGuiding = true;
      
      // Cacher tous les parkings
      parkingMarkers.forEach(marker => map.removeLayer(marker));
      
      // Supprimer les marqueurs et routes précédents
      if (destinationMarker) map.removeLayer(destinationMarker);
      if (routeLayer) map.removeLayer(routeLayer);
      if (secondRouteLayer) map.removeLayer(secondRouteLayer);
      
      // Ajouter le marqueur de destination finale
      destinationMarker = L.marker([clickLat, clickLng], { icon: destinationIcon }).addTo(map);
      
      // Calculer le trajet de l'utilisateur au parking
      await calculateRoute(userPosition, [nearestParking.lat, nearestParking.lng]);
      
      // Calculer le trajet du parking à la destination
      await calculateSecondRoute([nearestParking.lat, nearestParking.lng], [clickLat, clickLng]);
      
      // Afficher le bouton stopper
      document.getElementById('stop-guidance').style.display = 'block';
    });
  </script>
</body>
</html>
