/**
 * Module API Client
 * 
 * Principe SOLID appliqué : Single Responsibility Principle (SRP)
 * Ce module a une seule responsabilité : gérer toutes les communications avec l'API
 * 
 * Principe : Separation of Concerns - Toute la logique de communication HTTP est isolée ici
 */

/**
 * Classe responsable de la communication avec l'API backend
 */
class ApiClient {
    /**
     * Base URL de l'API (relative au dossier racine)
     */
    constructor() {
        this.baseUrl = 'api';
    }
    
    /**
     * Effectue une requête fetch avec gestion d'erreurs
     * 
     * @param {string} endpoint Endpoint de l'API
     * @param {Object} options Options de la requête fetch
     * @returns {Promise<any>} Données JSON décodées
     * @throws {Error} Si la requête échoue
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/${endpoint}`;
        
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Erreur HTTP ${response.status}`);
            }
            
            return await response.json();
            
        } catch (error) {
            console.error(`Erreur API [${endpoint}]:`, error);
            throw error;
        }
    }
    
    /**
     * Récupère tous les parkings pour une ville donnée
     * 
     * @param {string} city Code de la ville (ex: 'metz', 'london')
     * @returns {Promise<Object>} Données des parkings au format standardisé
     */
    async getParkings(city = 'metz') {
        const encodedCity = encodeURIComponent(city);
        return this.request(`getParkings.php?city=${encodedCity}`);
    }
    
    /**
     * Recherche des parkings par terme dans une ville
     * 
     * @param {string} query Terme de recherche
     * @param {string} city Code de la ville (ex: 'metz', 'london')
     * @returns {Promise<Object>} Données des parkings filtrés
     */
    async searchParkings(query, city = 'metz') {
        const encodedQuery = encodeURIComponent(query);
        const encodedCity = encodeURIComponent(city);
        return this.request(`getParkings.php?city=${encodedCity}&q=${encodedQuery}`);
    }
    
    /**
     * Calcule un itinéraire entre deux points
     * 
     * @param {number} lat1 Latitude départ
     * @param {number} lng1 Longitude départ
     * @param {number} lat2 Latitude arrivée
     * @param {number} lng2 Longitude arrivée
     * @returns {Promise<Object>} Données de l'itinéraire OSRM
     */
    async getRoute(lat1, lng1, lat2, lng2) {
        return this.request(
            `getRoute.php?lat1=${lat1}&lng1=${lng1}&lat2=${lat2}&lng2=${lng2}`
        );
    }
    
    /**
     * Récupère les préférences d'un utilisateur (ancienne méthode, dépréciée)
     * 
     * @param {number} userId ID de l'utilisateur
     * @returns {Promise<Object>} Préférences utilisateur
     * @deprecated Utiliser getUserProfile() à la place
     */
    async getUserPreferences(userId) {
        return this.request(`getUserPreferences.php?user_id=${userId}`);
    }
    
    /**
     * Récupère le profil de l'utilisateur connecté (utilise la session)
     * 
     * @returns {Promise<Object>} Profil utilisateur complet
     */
    async getUserProfile() {
        return this.request('getUserProfile.php');
    }
}

// Export pour utilisation dans d'autres modules
export default ApiClient;
