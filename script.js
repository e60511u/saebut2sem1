const carte = L.map('map').setView([49.119, 6.175], 13);

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
let destinationFinale = null;
let parkingIntermediaire = null;

// Variable pour stocker l'orientation
let orientationActuelle = 0;
let suivreUtilisateur = true;

// Obtenir l'orientation de l'appareil
if (window.DeviceOrientationEvent) {
  window.addEventListener('deviceorientationabsolute', (event) => {
    if (event.absolute && event.alpha !== null) {
      orientationActuelle = event.alpha;
      if (marqueurUtilisateur) {
        const icon = marqueurUtilisateur.getElement();
        if (icon) {
          icon.style.transform = `rotate(${orientationActuelle}deg)`;
          icon.style.transformOrigin = 'center';
        }
      }
    }
  });
  
  // Pour les appareils qui ne supportent pas deviceorientationabsolute
  window.addEventListener('deviceorientation', (event) => {
    if (event.alpha !== null) {
      orientationActuelle = event.webkitCompassHeading || event.alpha;
      if (marqueurUtilisateur) {
        const icon = marqueurUtilisateur.getElement();
        if (icon) {
          icon.style.transform = `rotate(${orientationActuelle}deg)`;
          icon.style.transformOrigin = 'center';
        }
      }
    }
  });
}



// Créer une icône personnalisée avec un cœur
const favoriteIcon = L.divIcon({
  html: '<div style="font-size: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">❤️</div>',
  iconSize: [30, 30],
  iconAnchor: [15, 30],
  popupAnchor: [0, -30],
  className: 'favorite-marker'
});

// Icône normale pour les parkings non favoris
const normalIcon = L.divIcon({
  html: '<div style="font-size: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🅿️</div>',
  iconSize: [30, 30],
  iconAnchor: [15, 30],
  popupAnchor: [0, -30],
  className: 'normal-marker'
});

// Fonction pour vérifier si un parking est favori
function isFavorite(parkingId) {
  return userFavorites.some(fav => fav.ref_parking_api === parkingId);
}




// Géolocalisation
function mettreAJourPositionUtilisateur() {
  navigator.geolocation.getCurrentPosition((position) => {
    positionUtilisateur = [position.coords.latitude, position.coords.longitude];
    
    if (marqueurUtilisateur) {
      marqueurUtilisateur.setLatLng(positionUtilisateur);
      // Suivre l'utilisateur si le guidage est actif et le switch est activé
      if (estEnGuidage && suivreUtilisateur) {
        carte.setView(positionUtilisateur, 17, { animate: true });
      }
    } else {
      marqueurUtilisateur = L.marker(positionUtilisateur, { icon: iconeUtilisateur, rotationAngle: orientationActuelle }).addTo(carte);
      carte.setView(positionUtilisateur, 15);
    }
    
    // Recalculer l'itinéraire si en guidage
    if (estEnGuidage && marqueurDestination) {
      if (destinationFinale !== null && parkingIntermediaire !== null) {
        // Guidage avec parking intermédiaire - ne recalculer que l'itinéraire principal
        calculerItineraire(positionUtilisateur, [parkingIntermediaire.lat, parkingIntermediaire.lng]);
        // L'itinéraire secondaire reste fixe et n'est pas recalculé
      } else {
        // Guidage direct vers un parking
        const destLatLng = marqueurDestination.getLatLng();
        calculerItineraire(positionUtilisateur, [destLatLng.lat, destLatLng.lng]);
      }
    }
  });
}

mettreAJourPositionUtilisateur();
setInterval(mettreAJourPositionUtilisateur, 2000);

// Calculer l'itinéraire
// Fonction async car elle effectue un appel réseau (fetch) vers l'API OSRM et doit attendre la réponse avant de pouvoir tracer l'itinéraire sur la carte
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

  // Ne pas ajuster les bounds(partie de la carte affichée) si le suivi est actif pendant le guidage
  if (!estEnGuidage || !suivreUtilisateur) {
    carte.fitBounds(coucheItineraire.getBounds(), { padding: [50, 50] });
  }
}

// Récupérer et afficher les parkings
// Fonction async car elle effectue plusieurs appels fetch
async function chargerParkings() {
  try {
    // Charger les parkings avec disponibilité en temps réel
    const reponse1 = await fetch('https://maps.eurometropolemetz.eu/public/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=public:pub_tsp_sta&srsName=EPSG:4326&outputFormat=json');
    const donnees1 = await reponse1.json();
    
    // Charger les places supplémentaires
    const reponse2 = await fetch('https://maps.eurometropolemetz.eu/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=public:pub_acc_sta&srsName=EPSG:4326&outputFormat=json');
    const donnees2 = await reponse2.json();
    
    // Combiner les deux sources de données
    donneesParkings = {
      type: "FeatureCollection",
      features: [...donnees1.features, ...donnees2.features]
    };
    
    marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
    marqueursParkings = [];
    
    // Ne pas afficher les parkings si on est en guidage
    if (estEnGuidage) {
      return;
    }
    
    // Afficher les parkings avec disponibilité
    donnees1.features.forEach(feature => {
      const coords = feature.geometry.coordinates;
      const nom = feature.properties.lib || 'Parking';
      const disponibles = feature.properties.place_libre;
      const total = feature.properties.place_total;
      const type = feature.properties.typ || '';
      const cout = feature.properties.cout;
      
      const iconeParking = L.divIcon({
        html: `<div style="background: #8A0808; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
        className: '',
        iconSize: [12, 12],
        iconAnchor: [6, 6]
      });
      
      // Construire le contenu du popup en fonction des données disponibles
      let infosDisponibilite = '';
      if (disponibles !== null && total !== null) {
        infosDisponibilite = `Disponibles: ${disponibles}/${total}<br>`;
      } else if (total !== null) {
        infosDisponibilite = `Total: ${total} places<br>`;
      }
      
      const contenuPopup = `
        <div>
          <b>${nom}</b><br>
          ${type && type !== 'aérien' ? `Type: ${type}<br>` : ''}
          ${infosDisponibilite}
          ${cout !== null ? `Coût: ${cout}<br>` : ''}
          <button onclick="demarrerGuidage(${coords[1]}, ${coords[0]})" style="margin-top: 10px; padding: 8px 16px; background: #8A0808; color: white; border: none; border-radius: 5px; cursor: pointer;">M'y guider</button>
        </div>
      `;
      
      const marqueur = L.marker([coords[1], coords[0]], { icon: iconeParking })
        .bindPopup(contenuPopup)
        .addTo(carte);
      
      marqueursParkings.push(marqueur);
    });


    // Afficher dans le popUp les favoris ou pas 
    function createPopupContent(parking) {
  const isFav = isFavorite(parking.id);
  const customName = getFavoriteCustomName(parking.id);
  
  let content = `
    <div class="parking-popup">
      <h3>${parking.nom}</h3>
      ${customName ? `<p><i>${customName}</i></p>` : ''}
      <p>Places: ${parking.places_dispo}</p>
      <form method="POST" action="">
        <input type="hidden" name="parking_id" value="${parking.id}">
        ${!isFav ? `
          <input type="text" name="custom_name" placeholder="Nom personnalisé (optionnel)">
          <button type="submit" name="add_favorite">❤️ Ajouter aux favoris</button>
        ` : `
          <button type="submit" name="remove_favorite">💔 Retirer des favoris</button>
        `}
      </form>
    </div>
  `;
  
  return content;
}

// Fonction pour vérifier si un parking est favori
function isFavorite(parkingId) {
  return userFavorites.some(fav => fav.ref_parking_api === parkingId);
}

// Fonction pour obtenir le nom personnalisé d'un favori
function getFavoriteCustomName(parkingId) {
  const favorite = userFavorites.find(fav => fav.ref_parking_api === parkingId);
  return favorite ? favorite.nom_custom : null;
}

    
    // Afficher les places de stationnement supplémentaires
    donnees2.features.forEach(feature => {
      const coords = feature.geometry.coordinates;
      const voie = feature.properties.voie || 'Parking';
      const quartier = feature.properties.quartier || '';
      const nombre = feature.properties.nombre || 1;
      const config = feature.properties.config || '';
      
      const iconeParking2 = L.divIcon({
        html: `<div style="background: #808080; width: 10px; height: 10px; border-radius: 50%; border: 2px solid white;"></div>`,
        className: '',
        iconSize: [10, 10],
        iconAnchor: [5, 5]
      });
      
      const contenuPopup = `
        <div>
          <b>${voie}</b><br>
          ${quartier ? `Quartier: ${quartier}<br>` : ''}
          ${nombre > 1 ? `${nombre} places<br>` : ''}
          ${config ? `Configuration: ${config}<br>` : ''}
          <button onclick="demarrerGuidage(${coords[1]}, ${coords[0]})" style="margin-top: 10px; padding: 8px 16px; background: #808080; color: white; border: none; border-radius: 5px; cursor: pointer;">M'y guider</button>
        </div>
      `;
      
      const marqueur = L.marker([coords[1], coords[0]], { icon: iconeParking2 })
        .bindPopup(contenuPopup)
        .addTo(carte);
      
      marqueursParkings.push(marqueur);
    });
  } catch (erreur) {
    console.error('Erreur lors du chargement des parkings:', erreur);
  }
}

chargerParkings();
setInterval(chargerParkings, 60000);

// Démarrer le guidage vers un parking
window.demarrerGuidage = function(lat, lng) {
  if (!positionUtilisateur) return;
  
  estEnGuidage = true;
  
  // Réinitialiser les variables de destination
  destinationFinale = null;
  parkingIntermediaire = null;
  
  // Cacher tous les parkings
  marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
  
  // Supprimer le marqueur de destination précédent
  if (marqueurDestination) carte.removeLayer(marqueurDestination);
  if (coucheItineraire) carte.removeLayer(coucheItineraire);
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);
  
  // Ajouter le marqueur de destination
  marqueurDestination = L.marker([lat, lng], { icon: iconeDestination }).addTo(carte);
  
  // Calculer l'itinéraire
  calculerItineraire(positionUtilisateur, [lat, lng]);
  
  // Afficher le bouton stopper et cacher le bouton nearest-parking
  document.getElementById('stop-guidance').classList.remove('hidden');
  document.getElementById('nearest-parking').classList.add('hidden');
  document.getElementById('search-bar').style.display = 'none';
  document.getElementById('follow-switch-container').classList.remove('hidden');
  
  // Fermer le popup
  carte.closePopup();
};

// Stopper le guidage
document.getElementById('stop-guidance').addEventListener('click', function() {
  // D'abord désactiver le guidage pour éviter les recalculs
  estEnGuidage = false;
  destinationFinale = null;
  parkingIntermediaire = null;
  
  // Supprimer toutes les couches d'itinéraire avec vérification
  if (coucheItineraire) {
    try {
      carte.removeLayer(coucheItineraire);
    } catch (e) {
      console.log('Erreur lors de la suppression de coucheItineraire:', e);
    }
    coucheItineraire = null;
  }
  
  if (coucheItineraireSecondaire) {
    try {
      carte.removeLayer(coucheItineraireSecondaire);
    } catch (e) {
      console.log('Erreur lors de la suppression de coucheItineraireSecondaire:', e);
    }
    coucheItineraireSecondaire = null;
  }
  
  // Supprimer le marqueur de destination
  if (marqueurDestination) {
    try {
      carte.removeLayer(marqueurDestination);
    } catch (e) {
      console.log('Erreur lors de la suppression du marqueurDestination:', e);
    }
    marqueurDestination = null;
  }
  
  // Réafficher les parkings
  chargerParkings();
  
  // Cacher le bouton stopper et réafficher le bouton nearest-parking
  this.classList.add('hidden');
  document.getElementById('nearest-parking').classList.remove('hidden');
  document.getElementById('search-bar').style.display = '';
  document.getElementById('follow-switch-container').classList.add('hidden');
});

// Switch pour suivre l'utilisateur
document.getElementById('follow-user').addEventListener('change', function() {
  suivreUtilisateur = this.checked;
  if (suivreUtilisateur && estEnGuidage && positionUtilisateur) {
    carte.setView(positionUtilisateur, 17, { animate: true });
  }
});

// Bouton parking le plus proche
document.getElementById('nearest-parking').addEventListener('click', function() {
  if (!positionUtilisateur) {
    alert('Position utilisateur non disponible');
    return;
  }
  
  const parkingLePlusProche = trouverParkingLePlusProche(positionUtilisateur[0], positionUtilisateur[1]);
  
  if (!parkingLePlusProche) {
    alert('Aucun parking trouvé');
    return;
  }
  
  // Démarrer le guidage vers le parking le plus proche
  demarrerGuidage(parkingLePlusProche.lat, parkingLePlusProche.lng);
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
// Fonction async car elle effectue un appel réseau fetch
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

// Recherche de parkings
const inputRecherche = document.getElementById('site-search');
const suggestionsList = document.getElementById('suggestions');
const searchButton = document.getElementById('search-button');
// Fonction pour filtrer les parkings par nom
function filtrerParkings(recherche) {
  if (!donneesParkings || !recherche) return [];
  
  const rechercheMin = recherche.toLowerCase();
  return donneesParkings.features.filter(feature => {
    const nom = feature.properties.lib || feature.properties.voie || '';
    const quartier = feature.properties.quartier || '';
    return nom.toLowerCase().includes(rechercheMin) || quartier.toLowerCase().includes(rechercheMin);
  });
}

// Afficher les suggestions
function afficherSuggestions(parkings) {
  suggestionsList.innerHTML = '';
  
  if (parkings.length === 0) {
    suggestionsList.classList.add('hidden');
    return;
  }
  
  parkings.forEach(feature => {
    const coords = feature.geometry.coordinates;
    const nom = feature.properties.lib || feature.properties.voie || 'Parking';
    const disponibles = feature.properties.place_libre || 0;
    const total = feature.properties.place_total || 0;
    const quartier = feature.properties.quartier || '';
    
    const item = document.createElement('div');
    item.className = 'suggestion-item';
    
    // Affichage différent selon le type de parking
    if (feature.properties.lib) {
      // Parking classique avec disponibilité
      let infoDisponibilite = '';
      if (disponibles !== null && total !== null) {
        infoDisponibilite = `Disponibles: ${disponibles}/${total}`;
      } else if (total !== null) {
        infoDisponibilite = `${total} places`;
      } else {
        infoDisponibilite = 'Informations non disponibles';
      }
      
      item.innerHTML = `
        <strong>${nom}</strong>
        <div class="parking-info">${infoDisponibilite}</div>
      `;
    } else {
      // Place supplémentaire
      item.innerHTML = `
        <strong>${nom}</strong>
        <div class="parking-info">${quartier ? `Quartier: ${quartier}` : ''}</div>
      `;
    }
    
    item.addEventListener('click', () => {
      inputRecherche.value = nom;
      suggestionsList.classList.add('hidden');
      // Centrer sur le parking et ouvrir le popup
      carte.setView([coords[1], coords[0]], 17);
      // Trouver le marqueur correspondant et ouvrir son popup
      marqueursParkings.forEach(marqueur => {
        if (marqueur.getLatLng().lat === coords[1] && marqueur.getLatLng().lng === coords[0]) {
          marqueur.openPopup();
        }
      });
    });
    
    suggestionsList.appendChild(item);
  });
  
  suggestionsList.classList.remove('hidden');
}

// Écouter les changements dans l'input
inputRecherche.addEventListener('input', (e) => {
  const recherche = e.target.value.trim();
  
  if (recherche.length < 2) {
    suggestionsList.classList.add('hidden');
    return;
  }
  
  const parkingsFiltres = filtrerParkings(recherche);
  afficherSuggestions(parkingsFiltres);
});

// Cacher les suggestions lors du clic ailleurs
document.addEventListener('click', (e) => {
  if (!e.target.closest('#search-bar')) {
    suggestionsList.classList.add('hidden');
  }
});

// Bouton de recherche
searchButton.addEventListener('click', () => {
  const recherche = inputRecherche.value.trim();
  
  if (recherche.length < 2) {
    alert('Veuillez entrer au moins 2 caractères');
    return;
  }
  
  const parkingsFiltres = filtrerParkings(recherche);
  
  if (parkingsFiltres.length === 0) {
    alert('Aucun parking trouvé');
    return;
  }
  
  // Centrer sur le premier résultat
  const coords = parkingsFiltres[0].geometry.coordinates;
  carte.setView([coords[1], coords[0]], 17);
  
  // Ouvrir le popup du premier résultat
  marqueursParkings.forEach(marqueur => {
    if (marqueur.getLatLng().lat === coords[1] && marqueur.getLatLng().lng === coords[0]) {
      marqueur.openPopup();
    }
  });
  
  suggestionsList.classList.add('hidden');
});

// Permettre la recherche avec la touche Entrée
inputRecherche.addEventListener('keypress', (e) => {
  if (e.key === 'Enter') {
    searchButton.click();
  }
});

// Clic sur la carte
carte.on('click', async function(e) {
  // Fonction async car elle appelle calculerItineraire() et calculerItineraireSecondaire()
  if (estEnGuidage) return;
  if (!positionUtilisateur) return;
  
  const latClic = e.latlng.lat;
  const lngClic = e.latlng.lng;
  
  // Trouver le parking le plus proche
  const parkingLePlusProche = trouverParkingLePlusProche(latClic, lngClic);
  
  if (!parkingLePlusProche) return;
  
  estEnGuidage = true;
  
  // Supprimer les anciennes routes et marqueurs
  if (marqueurDestination) carte.removeLayer(marqueurDestination);
  if (coucheItineraire) carte.removeLayer(coucheItineraire);
  if (coucheItineraireSecondaire) carte.removeLayer(coucheItineraireSecondaire);
  
  // Stocker la destination finale et le parking intermédiaire
  destinationFinale = { lat: latClic, lng: lngClic };
  parkingIntermediaire = parkingLePlusProche;
  
  // Cacher tous les parkings
  marqueursParkings.forEach(marqueur => carte.removeLayer(marqueur));
  
  // Ajouter le marqueur de destination finale
  marqueurDestination = L.marker([latClic, lngClic], { icon: iconeDestination }).addTo(carte);
  
  // Calculer le trajet de l'utilisateur au parking
  await calculerItineraire(positionUtilisateur, [parkingLePlusProche.lat, parkingLePlusProche.lng]);
  
  // Calculer le trajet du parking à la destination
  await calculerItineraireSecondaire([parkingLePlusProche.lat, parkingLePlusProche.lng], [latClic, lngClic]);
  
  // Afficher le bouton stopper et cacher le bouton nearest-parking
  document.getElementById('stop-guidance').classList.remove('hidden');
  document.getElementById('nearest-parking').classList.add('hidden');
  document.getElementById('search-bar').style.display = 'none';
  document.getElementById('follow-switch-container').classList.remove('hidden');
});
