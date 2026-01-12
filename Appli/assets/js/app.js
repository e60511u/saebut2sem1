/**
 * Module principal de l'application
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Ce module orchestre les interactions entre les différents composants
 * 
 * Principe : Separation of Concerns - La logique métier de l'application est ici
 */

import ApiClient from './api_client.js';
import MapManager from './map.js';

/**
 * Classe principale de l'application
 * Principe : Orchestration des différents modules
 */
class ParkingApp {
    constructor() {
        this.apiClient = new ApiClient();
        this.mapManager = new MapManager('map');
        this.parkingsData = null;
        this.userPreferences = null;
        this.isUserLoggedIn = false;
        this.currentCity = 'metz'; // Ville par défaut
        this.citiesConfig = null; // Sera chargé depuis l'API
        
        // Éléments DOM
        this.searchInput = null;
        this.suggestionsList = null;
        this.searchButton = null;
        this.nearestParkingBtn = null;
        this.stopGuidanceBtn = null;
        this.followUserCheckbox = null;
        this.citySelector = null;
        
        this.init();
    }
    
    /**
     * Initialise l'application
     */
    async init() {
        // Initialiser la carte
        this.mapManager.init();
        
        // Attendre que la carte soit prête
        this.mapManager.map.whenReady(() => {
            // Attendre un peu pour que le DOM soit complètement chargé
            setTimeout(() => {
                this.setupDOM();
                this.setupEventListeners();
                this.loadParkings();
                this.loadUserPreferences();
            }, 100);
        });
    }
    
    /**
     * Configure les références aux éléments DOM
     */
    setupDOM() {
        this.searchInput = document.getElementById('search-input');
        this.suggestionsList = document.getElementById('suggestions');
        this.searchButton = document.getElementById('search-button');
        this.nearestParkingBtn = document.getElementById('nearest-parking-btn');
        this.stopGuidanceBtn = document.getElementById('stop-guidance-btn');
        this.followUserCheckbox = document.getElementById('follow-user-checkbox');
        this.toggleFilterBtn = document.getElementById('toggle-filter-btn');
        this.citySelector = document.getElementById('city-selector');
        this.filterRealParkings = false;
        
        // Charger la configuration des villes
        this.loadCitiesConfig();
    }
    
    /**
     * Charge la configuration des villes
     */
    loadCitiesConfig() {
        // Configuration statique des villes
        this.citiesConfig = {
            'metz': {
                center: { lat: 49.119, lng: 6.176 },
                zoom: 13
            },
            'london': {
                center: { lat: 51.507, lng: -0.127 },
                zoom: 12
            }
        };
    }
    
    /**
     * Configure les écouteurs d'événements
     */
    setupEventListeners() {
        // Sélecteur de ville
        if (this.citySelector) {
            this.citySelector.addEventListener('change', (e) => {
                const cityCode = e.target.value;
                this.changeCity(cityCode);
            });
        }
        
        // Recherche de parkings
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });
            
            this.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch();
                }
            });
        }
        
        if (this.searchButton) {
            this.searchButton.addEventListener('click', () => {
                this.performSearch();
            });
        }
        
        // Bouton parking le plus proche
        if (this.nearestParkingBtn) {
            this.nearestParkingBtn.addEventListener('click', () => {
                this.guideToNearestParking();
            });
        }
        
        // Bouton arrêter guidage
        if (this.stopGuidanceBtn) {
            this.stopGuidanceBtn.addEventListener('click', () => {
                this.stopGuidance();
            });
        }
        
        // Checkbox suivi utilisateur
        if (this.followUserCheckbox) {
            this.followUserCheckbox.addEventListener('change', (e) => {
                this.mapManager.setFollowUser(e.target.checked);
            });
        }
        
        // Bouton filtre parkings réels
        if (this.toggleFilterBtn) {
            this.toggleFilterBtn.addEventListener('click', () => {
                this.filterRealParkings = !this.filterRealParkings;
                this.mapManager.setFilterRealParkings(this.filterRealParkings);
                this.toggleFilterBtn.textContent = this.filterRealParkings 
                    ? 'Afficher tous les parkings' 
                    : 'Masquer les parkings de rue';
                
                // Rafraîchir l'affichage courant
                if (this.mapManager.isGuidanceActive) return;
                
                // Réafficher les parkings avec le nouveau filtre
                if (this.parkingsData) {
                    // Vider les marqueurs actuels
                    this.mapManager.clearParkingMarkers();
                    
                    // Réafficher avec le filtre
                    if (this.userPreferences) {
                        this.applyFilters();
                    } else {
                        this.mapManager.displayParkings(this.parkingsData, false);
                    }
                }
            });
        }
        
        // Écouter les clics sur les boutons de guidage dans les popups
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-guidance')) {
                const lat = parseFloat(e.target.dataset.lat);
                const lng = parseFloat(e.target.dataset.lng);
                const name = e.target.dataset.name || null;
                this.startGuidance(lat, lng, name);
            }
        });
        
        // Écouter les événements de la carte
        this.mapManager.map.on('guidance:started', (e) => {
            this.onGuidanceStarted(e.destinationName);
        });
        
        this.mapManager.map.on('guidance:routefound', (e) => {
            this.onRouteFound(e.distance, e.time, e.instructions);
        });
        
        this.mapManager.map.on('guidance:stopped', () => {
            this.onGuidanceStopped();
        });
        
        // Références aux éléments du panneau de guidage
        this.guidancePanel = document.getElementById('guidance-panel');
        this.guidanceDestinationName = document.getElementById('guidance-destination-name');
        this.guidanceSummary = document.getElementById('guidance-summary');
        this.guidanceInstructions = document.getElementById('guidance-instructions');
        this.searchBar = document.getElementById('search-bar');
        
        // Cacher les suggestions au clic ailleurs
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#search-bar')) {
                this.hideSuggestions();
            }
        });
    }
    
    /**
     * Charge les parkings depuis l'API
     */
    async loadParkings() {
        try {
            console.log('Chargement des parkings pour:', this.currentCity);
            this.parkingsData = await this.apiClient.getParkings(this.currentCity);
            console.log('Parkings chargés:', this.parkingsData);
            
            // Appliquer les filtres si les préférences sont déjà chargées
            if (this.userPreferences) {
                this.applyFilters();
            } else {
                // Sinon, afficher tous les parkings
                this.mapManager.displayParkings(this.parkingsData);
            }
            
            // Recharger toutes les 30 secondes
            if (!this.refreshInterval) {
                this.refreshInterval = setInterval(() => {
                    this.refreshParkings();
                }, 30000);
            }
            
        } catch (error) {
            console.error('Erreur lors du chargement des parkings:', error);
            this.showError('Impossible de charger les parkings: ' + error.message);
        }
    }
    
    /**
     * Rafraîchit les données des parkings
     */
    async refreshParkings() {
        try {
            this.parkingsData = await this.apiClient.getParkings(this.currentCity);
            
            // Ne pas réafficher si le guidage est actif
            if (!this.mapManager.isGuidanceActive) {
                // Appliquer les filtres si disponibles
                if (this.userPreferences) {
                    this.applyFilters();
                } else {
                    this.mapManager.displayParkings(this.parkingsData);
                }
            }
        } catch (error) {
            console.error('Erreur lors du rafraîchissement des parkings:', error);
        }
    }
    
    /**
     * Change la ville et recharge les données
     * 
     * @param {string} cityCode Code de la ville
     */
    async changeCity(cityCode) {
        if (this.currentCity === cityCode) {
            return; // Déjà sur cette ville
        }
        
        console.log('Changement de ville:', cityCode);
        this.currentCity = cityCode;
        
        // Recentrer la carte
        if (this.citiesConfig && this.citiesConfig[cityCode]) {
            this.mapManager.centerOnCity(cityCode, this.citiesConfig);
        }
        
        // Vider les marqueurs
        this.mapManager.clearParkingMarkers();
        
        // Recharger les parkings
        await this.loadParkings();
    }
    
    /**
     * Charge les préférences utilisateur
     */
    async loadUserPreferences() {
        try {
            // Utiliser le nouvel endpoint qui utilise la session
            this.userPreferences = await this.apiClient.getUserProfile();
            this.isUserLoggedIn = true;
            
            // Appliquer les filtres après avoir chargé les parkings
            if (this.parkingsData) {
                this.applyFilters();
            }
        } catch (error) {
            console.log('Utilisateur non connecté ou erreur:', error.message);
            // Utilisateur non connecté, continuer sans filtres
            this.userPreferences = null;
            this.isUserLoggedIn = false;
        }
    }
    
    /**
     * Applique les filtres selon les préférences utilisateur
     * 
     * Principe : Single Responsibility Principle (SRP)
     * Cette méthode a une seule responsabilité : filtrer les parkings selon les préférences
     */
    applyFilters() {
        if (!this.userPreferences || !this.parkingsData) {
            return;
        }
        
        // Gérer le nouveau format standardisé
        let parkings = [];
        if (Array.isArray(this.parkingsData.parkings)) {
            parkings = this.parkingsData.parkings;
        } else if (Array.isArray(this.parkingsData)) {
            parkings = this.parkingsData;
        } else {
            return; // Format non reconnu
        }
        
        const filteredParkings = parkings.filter(parking => {
            
            // Filtre 1 : Préférence de coût
            if (this.userPreferences.preference_cout === 'GRATUIT') {
                // Masquer les parkings payants
                const cost = (parking.cost || '').toLowerCase();
                if (cost.includes('payant') || 
                    cost.includes('€') || 
                    cost.includes('£') ||
                    (parseFloat(cost) > 0)) {
                    return false;
                }
            } else if (this.userPreferences.preference_cout === 'PAYANT') {
                // Masquer les parkings gratuits
                const cost = (parking.cost || '').toLowerCase();
                if (cost.includes('gratuit') || 
                    cost === '0' || 
                    cost === '' ||
                    cost.includes('non spécifié')) {
                    return false;
                }
            }
            
            // Filtre 2 : Accessibilité PMR
            if (this.userPreferences.est_pmr) {
                // Vérifier si le parking est accessible PMR (format standardisé)
                if (parking.is_pmr === true) {
                    return true;
                }
                // Si pas d'info claire, on garde le parking (mieux vaut trop que pas assez)
            }
            
            // Filtre 3 : Type de véhicule et motorisation
            if (this.userPreferences.vehicules && this.userPreferences.vehicules.length > 0) {
                const vehicule = this.userPreferences.vehicules[0]; // Prendre le premier véhicule
                
                // Si l'utilisateur a un vélo
                if (vehicule.libelle_type === 'Velo') {
                    // Les vélos peuvent se garer partout, pas de filtre spécial
                    return true;
                }
                
                // Si l'utilisateur a une moto
                if (vehicule.libelle_type === 'Moto') {
                    // Les motos peuvent généralement se garer partout
                    return true;
                }
                
                // Si l'utilisateur a une voiture électrique
                if (vehicule.libelle_moto === 'Electrique') {
                    // Vérifier si le parking a des bornes de recharge (format standardisé)
                    if (parking.has_electric_charging === true) {
                        return true;
                    }
                    // Sinon, on garde le parking (on ne peut pas être sûr qu'il n'y a pas de bornes)
                }
            }
            
            return true;
        });
        
        const filteredData = {
            city: this.parkingsData.city || this.currentCity,
            parkings: filteredParkings,
            count: filteredParkings.length
        };
        
        if (!this.mapManager.isGuidanceActive) {
            this.mapManager.displayParkings(filteredData, false);
        }
    }
    
    /**
     * Gère la saisie dans le champ de recherche
     * 
     * @param {string} query Terme de recherche
     */
    handleSearchInput(query) {
        if (query.length < 2) {
            this.hideSuggestions();
            return;
        }
        
        const filtered = this.filterParkings(query);
        this.showSuggestions(filtered);
    }
    
    /**
     * Filtre les parkings selon un terme de recherche
     * 
     * @param {string} query Terme de recherche
     * @returns {Array} Parkings filtrés
     */
    filterParkings(query) {
        // Gérer le nouveau format standardisé
        let parkings = [];
        if (this.parkingsData && Array.isArray(this.parkingsData.parkings)) {
            parkings = this.parkingsData.parkings;
        } else if (this.parkingsData && Array.isArray(this.parkingsData)) {
            parkings = this.parkingsData;
        } else if (this.parkingsData && this.parkingsData.features) {
            // Format GeoJSON ancien (rétrocompatibilité)
            parkings = this.parkingsData.features.map(feature => ({
                id: feature.id || feature.properties?.id,
                name: this.mapManager.cleanParkingName(feature.properties || {}),
                lat: feature.geometry?.coordinates?.[1],
                lng: feature.geometry?.coordinates?.[0],
                total_places: feature.properties?.place_total || feature.properties?.total,
                available_places: feature.properties?.place_libre || feature.properties?.dispo,
                status: 'UNKNOWN',
                cost: feature.properties?.cout || null,
                is_pmr: false,
                has_electric_charging: false,
                properties: feature.properties // Garder pour isValidParking
            }));
        }
        
        if (parkings.length === 0) {
            return [];
        }
        
        const queryLower = query.toLowerCase();
        return parkings.filter(parking => {
            // Si le filtre est actif, exclure les parkings de rue (uniquement pour format GeoJSON)
            if (this.filterRealParkings && parking.properties && !this.mapManager.isValidParking(parking.properties)) {
                return false;
            }
            const nom = (parking.name || '').toLowerCase();
            const quartier = (parking.additional_info?.quartier || '').toLowerCase();
            return nom.includes(queryLower) || quartier.includes(queryLower);
        });
    }
    
    /**
     * Affiche les suggestions de recherche
     * 
     * @param {Array} parkings Parkings à afficher (format standardisé)
     */
    showSuggestions(parkings) {
        if (!this.suggestionsList) return;
        
        if (parkings.length === 0) {
            this.hideSuggestions();
            return;
        }
        
        this.suggestionsList.innerHTML = '';
        
        parkings.slice(0, 5).forEach(parking => {
            const lat = parking.lat;
            const lng = parking.lng;
            const nom = parking.name || 'Parking';
            const disponibles = parking.available_places;
            const total = parking.total_places;
            
            if (lat === null || lng === null) {
                return; // Ignorer les parkings sans coordonnées
            }
            
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            
            let info = '';
            // Afficher les infos seulement si les valeurs sont valides
            if (disponibles !== null && disponibles !== undefined && 
                total !== null && total !== undefined) {
                info = `${disponibles}/${total} places`;
            } else if (total !== null && total !== undefined) {
                info = `${total} places`;
            }
            
            item.innerHTML = `
                <strong>${nom}</strong>
                ${info ? `<div class="parking-info">${info}</div>` : ''}
            `;
            
            item.addEventListener('click', () => {
                this.searchInput.value = nom;
                this.hideSuggestions();
                this.mapManager.map.setView([lat, lng], 17);
                
                // Ouvrir le popup du marqueur correspondant
                this.mapManager.parkingMarkers.forEach(marker => {
                    const markerLatLng = marker.getLatLng();
                    if (Math.abs(markerLatLng.lat - lat) < 0.0001 &&
                        Math.abs(markerLatLng.lng - lng) < 0.0001) {
                        marker.openPopup();
                    }
                });
            });
            
            this.suggestionsList.appendChild(item);
        });
        
        this.suggestionsList.classList.remove('hidden');
    }
    
    /**
     * Cache les suggestions
     */
    hideSuggestions() {
        if (this.suggestionsList) {
            this.suggestionsList.classList.add('hidden');
        }
    }
    
    /**
     * Effectue une recherche de parkings
     */
    async performSearch() {
        const query = this.searchInput.value.trim();
        
        if (query.length < 2) {
            this.showError('Veuillez entrer au moins 2 caractères');
            return;
        }
        
        try {
            const results = await this.apiClient.searchParkings(query, this.currentCity);
            
            // Gérer le nouveau format standardisé
            let parkings = [];
            if (results && Array.isArray(results.parkings)) {
                parkings = results.parkings;
            } else if (results && Array.isArray(results)) {
                parkings = results;
            } else if (results && results.features) {
                // Format GeoJSON ancien (rétrocompatibilité)
                parkings = results.features.map(feature => ({
                    id: feature.id || feature.properties?.id,
                    name: this.mapManager.cleanParkingName(feature.properties || {}),
                    lat: feature.geometry?.coordinates?.[1],
                    lng: feature.geometry?.coordinates?.[0],
                    total_places: feature.properties?.place_total || feature.properties?.total,
                    available_places: feature.properties?.place_libre || feature.properties?.dispo,
                    status: 'UNKNOWN',
                    cost: feature.properties?.cout || null,
                    is_pmr: false,
                    has_electric_charging: false,
                    properties: feature.properties // Garder pour isValidParking
                }));
            }
            
            if (parkings.length === 0) {
                this.showError('Aucun parking trouvé');
                return;
            }
            
            // Filtrer les résultats si le filtre est activé (uniquement pour format GeoJSON)
            if (this.filterRealParkings) {
                parkings = parkings.filter(p => {
                    if (p.properties) {
                        return this.mapManager.isValidParking(p.properties);
                    }
                    return true; // Format standardisé, pas de filtre de rue
                });
                
                if (parkings.length === 0) {
                    this.showError('Aucun parking valide trouvé (filtre actif)');
                    return;
                }
            }
            
            const searchResults = {
                city: results.city || this.currentCity,
                parkings: parkings,
                count: parkings.length
            };
            
            // Afficher les résultats filtrés sur la carte avec forceFitBounds pour la recherche
            this.mapManager.displayParkings(searchResults, true);
            
            // Centrer sur le premier résultat
            if (parkings.length > 0 && parkings[0].lat && parkings[0].lng) {
                this.mapManager.map.setView([parkings[0].lat, parkings[0].lng], 17);
            }
            
            this.hideSuggestions();
            
        } catch (error) {
            console.error('Erreur lors de la recherche:', error);
            this.showError('Erreur lors de la recherche: ' + error.message);
        }
    }
    
    /**
     * Guide vers le parking le plus proche
     */
    guideToNearestParking() {
        if (!this.mapManager.userPosition) {
            this.showError('Position utilisateur non disponible');
            return;
        }
        
        // Gérer le nouveau format standardisé
        let parkings = [];
        if (this.parkingsData && Array.isArray(this.parkingsData.parkings)) {
            parkings = this.parkingsData.parkings;
        } else if (this.parkingsData && Array.isArray(this.parkingsData)) {
            parkings = this.parkingsData;
        } else if (this.parkingsData && this.parkingsData.features) {
            // Format GeoJSON ancien (rétrocompatibilité)
            parkings = this.parkingsData.features.map(feature => ({
                id: feature.id || feature.properties?.id,
                name: this.mapManager.cleanParkingName(feature.properties || {}),
                lat: feature.geometry?.coordinates?.[1],
                lng: feature.geometry?.coordinates?.[0],
                total_places: feature.properties?.place_total || feature.properties?.total,
                available_places: feature.properties?.place_libre || feature.properties?.dispo,
                status: 'UNKNOWN',
                cost: feature.properties?.cout || null,
                is_pmr: false,
                has_electric_charging: false
            }));
        }
        
        if (parkings.length === 0) {
            this.showError('Aucun parking disponible');
            return;
        }
        
        // Filtrer les parkings de rue si le filtre est actif
        if (this.filterRealParkings) {
            parkings = parkings.filter(parking => {
                // Pour le format standardisé
                if (parking.lat && parking.lng && parking.name) {
                    return this.mapManager.isValidParkingStandardized(parking);
                }
                // Pour le format GeoJSON ancien (rétrocompatibilité)
                if (parking.properties) {
                    return this.mapManager.isValidParking(parking.properties);
                }
                return true; // Si on ne peut pas déterminer, on garde
            });
        }
        
        if (parkings.length === 0) {
            this.showError('Aucun parking valide trouvé (filtre actif)');
            return;
        }
        
        // Filtrer selon les préférences utilisateur si connecté
        if (this.userPreferences) {
            parkings = parkings.filter(parking => {
                // Filtre PMR
                if (this.userPreferences.est_pmr && !parking.is_pmr) {
                    return false;
                }
                // Filtre coût
                if (this.userPreferences.preference_cout === 'GRATUIT') {
                    const cost = (parking.cost || '').toLowerCase();
                    if (cost.includes('payant') || cost.includes('£') || parseFloat(cost) > 0) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        if (parkings.length === 0) {
            this.showError('Aucun parking correspondant à vos préférences');
            return;
        }
        
        const nearest = this.findNearestParkingStandardized(
            parkings,
            this.mapManager.userPosition[0],
            this.mapManager.userPosition[1]
        );
        
        if (!nearest) {
            this.showError('Aucun parking trouvé');
            return;
        }
        
        this.startGuidance(nearest.lat, nearest.lng, nearest.name);
    }
    
    /**
     * Trouve le parking le plus proche (format standardisé)
     * 
     * @param {Array} parkings Liste de parkings au format standardisé
     * @param {number} userLat Latitude de l'utilisateur
     * @param {number} userLng Longitude de l'utilisateur
     * @returns {Object|null} Parking le plus proche ou null
     */
    findNearestParkingStandardized(parkings, userLat, userLng) {
        let nearest = null;
        let minDistance = Infinity;
        
        parkings.forEach(parking => {
            if (parking.lat === null || parking.lng === null) {
                return;
            }
            
            const distance = this.mapManager.calculateDistance(
                userLat,
                userLng,
                parking.lat,
                parking.lng
            );
            
            if (distance < minDistance) {
                minDistance = distance;
                nearest = parking;
            }
        });
        
        return nearest;
    }
    
    /**
     * Démarre le guidage vers une destination
     * 
     * @param {number} lat Latitude
     * @param {number} lng Longitude
     * @param {string} destinationName Nom de la destination (optionnel)
     */
    async startGuidance(lat, lng, destinationName = null) {
        try {
            await this.mapManager.startGuidance(lat, lng, destinationName);
        } catch (error) {
            console.error('Erreur lors du démarrage du guidage:', error);
            this.showError('Impossible de démarrer le guidage');
        }
    }
    
    /**
     * Arrête le guidage
     */
    stopGuidance() {
        this.mapManager.stopGuidance();
    }
    
    /**
     * Appelé quand le guidage démarre
     */
    onGuidanceStarted(destinationName) {
        // Cacher les marqueurs de parkings
        this.mapManager.clearParkingMarkers();
        
        // Cacher la barre de recherche
        if (this.searchBar) {
            this.searchBar.classList.add('hidden');
        }
        
        // Afficher le panneau de guidage
        if (this.guidancePanel) {
            this.guidancePanel.classList.remove('hidden');
        }
        
        // Mettre à jour le nom de destination
        if (this.guidanceDestinationName) {
            this.guidanceDestinationName.textContent = destinationName || 'Destination';
        }
        
        // Afficher/masquer les boutons appropriés
        if (this.nearestParkingBtn) {
            this.nearestParkingBtn.classList.add('hidden');
        }
        if (this.followUserCheckbox && this.followUserCheckbox.parentElement) {
            this.followUserCheckbox.parentElement.classList.remove('hidden');
        }
    }
    
    /**
     * Appelé quand l'itinéraire est trouvé
     */
    onRouteFound(distance, time, instructions) {
        // Mettre à jour le résumé
        if (this.guidanceSummary) {
            const distanceText = distance < 1000 ? `${distance} m` : `${(distance / 1000).toFixed(1)} km`;
            const timeText = time < 60 ? `${time} min` : `${Math.floor(time / 60)}h${time % 60} min`;
            this.guidanceSummary.textContent = `${distanceText}, ${timeText}`;
        }
        
        // Afficher les instructions
        if (this.guidanceInstructions) {
            this.guidanceInstructions.innerHTML = '';
            
            if (instructions && instructions.length > 0) {
                instructions.slice(0, 3).forEach((instruction, index) => {
                    const instructionDiv = document.createElement('div');
                    instructionDiv.className = 'instruction-item';
                    
                    let icon = '';
                    let text = instruction.text || 'Continuez';
                    let distance = instruction.distance ? `${Math.round(instruction.distance)} m` : '';
                    
                    // Déterminer l'icône selon le type d'instruction
                    if (index === 0) {
                        icon = '<span class="instruction-icon start">A</span>';
                    } else if (index === instructions.length - 1) {
                        icon = '<span class="instruction-icon end">B</span>';
                    } else if (text.toLowerCase().includes('nord-est') || text.toLowerCase().includes('sud-est')) {
                        icon = '<span class="instruction-icon turn-right">↗</span>';
                    } else if (text.toLowerCase().includes('nord-ouest') || text.toLowerCase().includes('sud-ouest')) {
                        icon = '<span class="instruction-icon turn-left">↖</span>';
                    } else if (text.toLowerCase().includes('est')) {
                        icon = '<span class="instruction-icon turn-right">→</span>';
                    } else if (text.toLowerCase().includes('ouest')) {
                        icon = '<span class="instruction-icon turn-left">←</span>';
                    } else if (text.toLowerCase().includes('tout droit') || text.toLowerCase().includes('nord') || text.toLowerCase().includes('sud')) {
                        icon = '<span class="instruction-icon straight">↑</span>';
                    } else {
                        icon = '<span class="instruction-icon">•</span>';
                    }
                    
                    instructionDiv.innerHTML = `
                        ${icon}
                        <div class="instruction-content">
                            <div class="instruction-text">${text}</div>
                            ${distance ? `<div class="instruction-distance">${distance}</div>` : ''}
                        </div>
                    `;
                    
                    this.guidanceInstructions.appendChild(instructionDiv);
                });
            } else {
                // Message par défaut si pas d'instructions
                this.guidanceInstructions.innerHTML = '<div class="instruction-item"><div class="instruction-text">Itinéraire calculé</div></div>';
            }
        }
    }
    
    /**
     * Appelé quand le guidage s'arrête
     */
    onGuidanceStopped() {
        // Réafficher les parkings
        if (this.parkingsData) {
            if (this.userPreferences) {
                this.applyFilters();
            } else {
                this.mapManager.displayParkings(this.parkingsData);
            }
        }
        
        // Cacher le panneau de guidage
        if (this.guidancePanel) {
            this.guidancePanel.classList.add('hidden');
        }
        
        // Réafficher la barre de recherche
        if (this.searchBar) {
            this.searchBar.classList.remove('hidden');
        }
        
        // Afficher/masquer les boutons appropriés
        if (this.nearestParkingBtn) {
            this.nearestParkingBtn.classList.remove('hidden');
        }
        if (this.followUserCheckbox && this.followUserCheckbox.parentElement) {
            this.followUserCheckbox.parentElement.classList.add('hidden');
        }
    }
    
    /**
     * Affiche un message d'erreur
     * 
     * @param {string} message Message d'erreur
     */
    showError(message) {
        // Implémentation simple - à améliorer avec un système de notifications
        alert(message);
    }
}

// Initialiser l'application quand le DOM est prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ParkingApp();
    });
} else {
    new ParkingApp();
}
