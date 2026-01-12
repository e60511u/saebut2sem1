# Application Parking Metz - Mobilité

## 📋 Description

Application web de recherche et guidage vers les parkings de Metz, développée dans le cadre de la SAé 3.01 (BUT 2 Informatique).

## 🏗️ Architecture

### Structure des dossiers

```
Appli/
├── config/
│   └── db.php              # Connexion PDO Singleton à la base de données
├── api/
│   ├── getParkings.php     # Endpoint API : Récupération des parkings
│   ├── getRoute.php        # Endpoint API : Calcul d'itinéraire
│   └── getUserPreferences.php  # Endpoint API : Préférences utilisateur
├── includes/
│   ├── header.php          # En-tête HTML réutilisable
│   ├── footer.php          # Pied de page HTML réutilisable
│   └── navbar.php          # Barre de navigation (optionnelle)
├── assets/
│   ├── css/
│   │   └── style.css       # Feuille de style principale
│   └── js/
│       ├── api_client.js   # Module : Communication avec l'API
│       ├── map.js          # Module : Gestion de la carte Leaflet
│       └── app.js          # Module : Logique principale de l'application
└── index.php               # Point d'entrée principal
```

## 🎯 Principes SOLID appliqués

### Single Responsibility Principle (SRP)
- **`config/db.php`** : Responsabilité unique de gérer la connexion à la base de données
- **`api/*.php`** : Chaque endpoint a une seule responsabilité (récupérer parkings, calculer route, etc.)
- **`assets/js/api_client.js`** : Responsabilité unique de gérer les communications HTTP
- **`assets/js/map.js`** : Responsabilité unique de gérer l'affichage de la carte
- **`assets/js/app.js`** : Responsabilité unique d'orchestrer l'application

### Separation of Concerns
- **Séparation stricte** : SQL, PHP et HTML sont dans des fichiers différents
- **API séparée** : Les endpoints API ne renvoient que du JSON, pas de HTML
- **JavaScript modulaire** : Code organisé en modules ES6 avec responsabilités distinctes

### Don't Repeat Yourself (DRY)
- **Connexion BDD** : Singleton pour éviter les multiples connexions
- **Includes réutilisables** : Header et footer factorisés
- **Modules JavaScript** : Fonctions réutilisables dans des classes

## 🔧 Technologies utilisées

- **Frontend** : HTML5, CSS3, JavaScript (ES6+ Modules)
- **Backend** : PHP (sans framework)
- **Base de données** : MySQL/MariaDB
- **Cartographie** : Leaflet.js + Leaflet Routing Machine
- **APIs externes** :
  - Open Data Metz (parkings)
  - OSRM (calcul d'itinéraires)

## 📦 Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4+ avec extensions PDO et MySQL
- Base de données MySQL/MariaDB

### Configuration

1. **Base de données** :
   - Importer le script SQL `bdd/e40250u_sae301.sql`
   - Modifier les identifiants dans `config/db.php` :
     ```php
     $host = 'devbdd.iutmetz.univ-lorraine.fr';
     $dbname = 'e40250u_sae301';
     $username = 'votre_identifiant';
     $password = 'votre_mot_de_passe';
     ```

2. **Serveur web** :
   - Configurer le serveur pour pointer vers le dossier `Appli/`
   - S'assurer que les modules PHP sont activés (PDO, MySQL)

## 🚀 Utilisation

1. Ouvrir `index.php` dans un navigateur
2. Autoriser la géolocalisation si demandée
3. Les parkings s'affichent automatiquement sur la carte
4. Utiliser la barre de recherche pour trouver un parking
5. Cliquer sur "M'y guider" dans un popup pour démarrer la navigation

## 🔍 Fonctionnalités

### Cœur de l'application
- ✅ Affichage de la carte Leaflet centrée sur l'utilisateur
- ✅ Récupération des parkings via API Open Data Metz
- ✅ Marqueurs colorés selon la disponibilité (Vert/Orange/Rouge)
- ✅ Calcul et affichage d'itinéraire vers un parking
- ✅ Recherche de parkings par nom ou quartier

### Gestion des profils
- ✅ Récupération des préférences utilisateur depuis la BDD
- ✅ Filtrage des parkings selon :
  - Préférence de coût (GRATUIT/PAYANT/INDIFFERENT)
  - Accessibilité PMR
  - Type de véhicule et motorisation

## 📝 Notes techniques

### API Endpoints

#### `GET api/getParkings.php`
Récupère tous les parkings ou effectue une recherche.

**Paramètres optionnels** :
- `q` : Terme de recherche

**Réponse** : FeatureCollection GeoJSON

#### `GET api/getRoute.php`
Calcule un itinéraire entre deux points.

**Paramètres requis** :
- `lat1`, `lng1` : Coordonnées du point de départ
- `lat2`, `lng2` : Coordonnées du point d'arrivée

**Réponse** : Données OSRM de l'itinéraire

#### `GET api/getUserPreferences.php`
Récupère les préférences d'un utilisateur.

**Paramètres requis** :
- `user_id` : ID de l'utilisateur

**Réponse** : Objet JSON avec les préférences et véhicules

### Modules JavaScript

#### `ApiClient`
Classe responsable de toutes les communications avec l'API backend.

#### `MapManager`
Classe responsable de la gestion de la carte Leaflet et des marqueurs.

#### `ParkingApp`
Classe principale qui orchestre l'application.

## 🐛 Dépannage

### La carte ne s'affiche pas
- Vérifier que Leaflet.js est bien chargé
- Vérifier la console du navigateur pour les erreurs

### Les parkings ne se chargent pas
- Vérifier que l'API Open Data Metz est accessible
- Vérifier la console du navigateur pour les erreurs CORS
- Vérifier les logs PHP pour les erreurs serveur

### La géolocalisation ne fonctionne pas
- Vérifier que le navigateur autorise la géolocalisation
- Vérifier que le site est en HTTPS (requis pour la géolocalisation)

## 📄 Licence

Projet académique - SAé 3.01

## 👥 Auteurs

Étudiant BUT 2 Informatique
