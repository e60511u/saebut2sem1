const carte = L.map('map').setView([0, 0], 2);

// Utilisation d'OpenStreetMap (pas besoin de clé API)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(carte);

const iconeUtilisateur = L.icon({
  iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
  shadowUrl: '',
  iconSize: [25, 41],
  iconAnchor: [12, 41]
});

const iconeDestination = L.icon({
  iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
  shadowUrl: '',
  iconSize: [25, 41],
  iconAnchor: [12, 41]
});

let marqueurUtilisateur = null;
let positionUtilisateur = null;
let marqueurDestination = null;
let coucheItineraire = null;
let marqueursParkings = [];
let estEnGuidage = false;
let donneesParkings = null;
let coucheItineraireSecondaire = null;

// Géolocalisation
function mettreAJourPositionUtilisateur() {
  navigator.geolocation.getCurrentPosition((position) => {
    positionUtilisateur = [position.coords.latitude, position.coords.longitude];
    
    if (marqueurUtilisateur) {
      marqueurUtilisateur.setLatLng(positionUtilisateur);
    } else {
      marqueurUtilisateur = L.marker(positionUtilisateur, { icon: iconeUtilisateur }).addTo(carte);
      carte.setView(positionUtilisateur, 15);
    }
  });
}

mettreAJourPositionUtilisateur();
setInterval(mettreAJourPositionUtilisateur, 5000);

// Calculer l'itinéraire
async function calculerItineraire(debut, fin) {
  if (coucheItineraire) carte.removeLayer(coucheItineraire);

  const url = `https://router.project-osrm.org/route/v1/driving/${debut[1]},${debut[0]};${fin[1]},${fin[0]}?overview=full&geometries=geojson`;
  const reponse = await fetch(url);
  const donnees = await reponse.json();
  
  const coordonnees = donnees.routes[0].geometry.coordinates.map(p => [p[1], p[0]]);
  
  coucheItineraire = L.polyline(coordonnees, {
    color: '#8A0808',
    weight: 5,
    opacity: 0.7
  }).addTo(carte);

  carte.fitBounds(coucheItineraire.getBounds(), { padding: [50, 50] });
}

// Récupérer et afficher les parkings
async function chargerParkings() {
  try {
    const reponse = await fetch('https://maps.eurometropolemetz.eu/public/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=public:pub_tsp_sta&srsName=EPSG:4326&outputFormat=application%2Fjson&cql_filter=id%20is%20not%20null');
    const donnees = await reponse.json();
    
    donneesParkings = donnees;
    
    marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
    marqueursParkings = [];
    
    donnees.features.forEach(feature => {
      const coords = feature.geometry.coordinates;
      const nom = feature.properties.lib || 'Parking';
      const disponibles = feature.properties.place_libre || 0;
      const total = feature.properties.place_total || 0;
      
      const iconeParking = L.divIcon({
        html: `<div style="background: #8A0808; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
        className: '',
        iconSize: [12, 12],
        iconAnchor: [6, 6]
      });
      
      const contenuPopup = `
        <div>
          <b>${nom}</b><br>
          Disponibles: ${disponibles}/${total}<br>
          <button onclick="demarrerGuidage(${coords[1]}, ${coords[0]})" style="margin-top: 10px; padding: 8px 16px; background: #8A0808; color: white; border: none; border-radius: 5px; cursor: pointer;">M'y guider</button>
        </div>
      `;
      
      const marqueur = L.marker([coords[1], coords[0]], { icon: iconeParking })
        .bindPopup(contenuPopup)
        .addTo(carte);
      
      marqueursParkings.push(marqueur);
    });
  } catch (erreur) {
    console.error('Erreur lors du chargement des parkings:', erreur);
  }
}

chargerParkings();
setInterval(chargerParkings, 30000);

// Démarrer le guidage vers un parking
window.demarrerGuidage = function(lat, lng) {
  if (!positionUtilisateur) return;
  
  estEnGuidage = true;
  
  // Cacher tous les parkings
  marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
  
  // Supprimer le marqueur de destination précédent
  if (marqueurDestination) carte.removeLayer(marqueurDestination);
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);
  
  // Ajouter le marqueur de destination
  marqueurDestination = L.marker([lat, lng], { icon: iconeDestination }).addTo(carte);
  
  // Calculer l'itinéraire
  calculerItineraire(positionUtilisateur, [lat, lng]);
  
  // Afficher le bouton stopper
  document.getElementById('stop-guidance').classList.remove('hidden');
  
  // Fermer le popup
  carte.closePopup();
};

// Stopper le guidage
document.getElementById('stop-guidance').addEventListener('click', function() {
  estEnGuidage = false;
  
  // Supprimer l'itinéraire
  if (coucheItineraire) carte.removeLayer(coucheItineraire);
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);
  
  // Supprimer le marqueur de destination
  if (marqueurDestination) carte.removeLayer(marqueurDestination);
  
  // Réafficher les parkings
  chargerParkings();
  
  // Cacher le bouton stopper
  this.classList.add('hidden');
});

// Trouver le parking le plus proche
function trouverParkingLePlusProche(lat, lng) {
  if (!donneesParkings || !donneesParkings.features) return null;
  
  let plusProche = null;
  let distanceMin = Infinity;
  
  donneesParkings.features.forEach(feature => {
    const coords = feature.geometry.coordinates;
    const latParking = coords[1];
    const lngParking = coords[0];
    
    const distance = Math.sqrt(
      Math.pow(lat - latParking, 2) + Math.pow(lng - lngParking, 2)
    );
    
    if (distance < distanceMin) {
      distanceMin = distance;
      plusProche = { lat: latParking, lng: lngParking };
    }
  });
  
  return plusProche;
}

// Calculer le deuxième itinéraire (du parking à la destination)
async function calculerItineraireSecondaire(debut, fin) {
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);

  const url = `https://router.project-osrm.org/route/v1/driving/${debut[1]},${debut[0]};${fin[1]},${fin[0]}?overview=full&geometries=geojson`;
  const reponse = await fetch(url);
  const donnees = await reponse.json();
  
  const coordonnees = donnees.routes[0].geometry.coordinates.map(p => [p[1], p[0]]);
  
  coucheItineraireSecondaire = L.polyline(coordonnees, {
    color: '#808080',
    weight: 5,
    opacity: 0.7
  }).addTo(carte);
}

// Clic sur la carte
carte.on('click', async function(e) {
  if (estEnGuidage) return;
  if (!positionUtilisateur) return;
  
  const latClic = e.latlng.lat;
  const lngClic = e.latlng.lng;
  
  // Trouver le parking le plus proche
  const parkingLePlusProche = trouverParkingLePlusProche(latClic, lngClic);
  
  if (!parkingLePlusProche) return;
  
  estEnGuidage = true;
  
  // Cacher tous les parkings
  marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
  
  // Supprimer les marqueurs et routes précédents
  if (marqueurDestination) carte.removeLayer(marqueurDestination);
  if (coucheItineraire) carte.removeLayer(coucheItineraire);
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);
  
  // Ajouter le marqueur de destination finale
  marqueurDestination = L.marker([latClic, lngClic], { icon: iconeDestination }).addTo(carte);
  
  // Calculer le trajet de l'utilisateur au parking
  await calculerItineraire(positionUtilisateur, [parkingLePlusProche.lat, parkingLePlusProche.lng]);
  
  // Calculer le trajet du parking à la destination
  await calculerItineraireSecondaire([parkingLePlusProche.lat, parkingLePlusProche.lng], [latClic, lngClic]);
  
  // Afficher le bouton stopper
  document.getElementById('stop-guidance').classList.remove('hidden');
});
