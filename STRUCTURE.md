# 📁 STRUCTURE DU PROJET - SAE PARKING APP

## 🎯 Architecture MVC

Le projet suit maintenant une **architecture Modèle-Vue-Contrôleur (MVC)** professionnelle.

```
sae but 2 sem1/
│
├── 📄 index.html              # Page d'accueil (portail)
├── 📄 app.php                 # Carte interactive (MVC)
├── 📄 login.php               # Connexion (MVC)
├── 📄 register.php            # Inscription (MVC)
├── 📄 user_settings.php       # Paramètres utilisateur (MVC)
├── 📄 logout.php              # Déconnexion (MVC)
│
├── 📂 models/                 # 🔷 MODÈLES - Données & Logique Métier
│   ├── Database.php           #   Singleton de connexion BDD
│   ├── User.php               #   Gestion des utilisateurs
│   ├── Vehicle.php            #   Gestion des véhicules
│   └── Favorite.php           #   Gestion des favoris
│
├── 📂 views/                  # 🎨 VUES - Interface Utilisateur
│   ├── layouts/
│   │   └── base.php           #   Template de base réutilisable
│   ├── auth/
│   │   ├── login.php          #   Vue connexion
│   │   └── register.php       #   Vue inscription
│   ├── user/
│   │   └── settings.php       #   Vue paramètres utilisateur
│   └── parking/
│       └── map.php            #   Vue carte interactive
│
├── 📂 controllers/            # 🎮 CONTRÔLEURS - Logique de Coordination
│   ├── AuthController.php     #   Authentification & Sessions
│   ├── UserController.php     #   Gestion utilisateur & entités
│   └── ParkingController.php  #   Affichage carte
│
├── 📂 assets/                 # 🎨 Ressources Statiques
│   ├── css/
│   │   ├── style.css          #   Styles carte principale
│   │   ├── login.css          #   Styles connexion
│   │   ├── register.css       #   Styles inscription
│   │   └── user_settings.css  #   Styles paramètres
│   └── js/
│       └── script.js          #   Logique Leaflet (carte)
│
├── 📂 config/                 # ⚙️ Configuration
│   ├── db.php                 #   Configuration BDD (PDO)
│   └── db.example.php         #   Template de configuration
│
├── 📂 old_classic_version/    # 💾 Sauvegarde de l'ancienne version
│   ├── app.php                #   Ancienne version monolithique
│   ├── login.php              #   Ancienne version monolithique
│   ├── register.php           #   Ancienne version monolithique
│   ├── user_settings.php      #   Ancienne version monolithique
│   ├── logout.php             #   Ancienne version monolithique
│   ├── index.html             #   Ancien index
│   └── includes/              #   Anciens fichiers fonctionnels
│       ├── auth.php
│       └── user.php
│
├── 📂 Documentation           # 📚 Documentation Complète
│   ├── ARCHITECTURE_MVC.md    #   Guide détaillé de l'architecture MVC
│   ├── MVC_IMPLEMENTATION.md  #   Résumé de l'implémentation MVC
│   ├── STRUCTURE.md           #   Ce fichier (structure du projet)
│   ├── CHANGEMENTS.md         #   Résumé des modifications
│   ├── RECAP.md               #   Récapitulatif général
│   └── README.md              #   Documentation originale
│
└── 📂 Utilitaires             # 🛠️ Fichiers Utilitaires
    ├── check.php              #   Vérification de l'installation
    ├── db.sql                 #   Script de création BDD
    ├── .gitignore             #   Fichiers à ignorer par Git
    └── .env                   #   Variables d'environnement (à créer)
```

---

## 🏗️ Architecture MVC Détaillée

### 🔷 MODÈLES (`models/`)

Les modèles gèrent les **données** et la **logique métier**.

#### `Database.php` - Connexion à la base de données
- **Pattern**: Singleton
- **Méthodes**: 
  - `getInstance()` - Obtenir l'instance unique
  - `getConnection()` - Obtenir la connexion PDO

#### `User.php` - Gestion des utilisateurs
- **Méthodes**:
  - `findByIdentifier($identifier)` - Recherche par pseudo/email
  - `findById($id)` - Recherche par ID
  - `create($pseudo, $email, $password)` - Créer un utilisateur
  - `update($id, $data)` - Mettre à jour un utilisateur
  - `verifyPassword($password)` - Vérifier le mot de passe
  - `hydrate($data)` - Remplir l'objet avec des données
  - `toArray()` - Convertir en tableau

#### `Vehicle.php` - Gestion des véhicules
- **Méthodes**:
  - `findByUserId($userId)` - Liste des véhicules d'un utilisateur
  - `create()` - Ajouter un véhicule
  - `delete($vehicleId, $userId)` - Supprimer un véhicule
  - `getTypes()` - Types de véhicules disponibles
  - `getMotorisations()` - Motorisations disponibles

#### `Favorite.php` - Gestion des favoris
- **Méthodes**:
  - `findByUserId($userId)` - Liste des favoris d'un utilisateur
  - `create($userId, $parkingId, $customName)` - Ajouter un favori
  - `delete($favoriteId, $userId)` - Supprimer un favori

---

### 🎮 CONTRÔLEURS (`controllers/`)

Les contrôleurs gèrent la **logique de coordination** entre modèles et vues.

#### `AuthController.php` - Authentification
- **Méthodes**:
  - `showLogin()` - Afficher la vue de connexion
  - `login()` - Traiter la connexion
  - `showRegister()` - Afficher la vue d'inscription
  - `register()` - Traiter l'inscription
  - `logout()` - Déconnecter l'utilisateur
  - `isLoggedIn()` - Vérifier si l'utilisateur est connecté
  - `requireLogin()` - Rediriger si non connecté
  - `initSession($user)` - Initialiser la session utilisateur

#### `UserController.php` - Gestion utilisateur
- **Méthodes**:
  - `showSettings()` - Afficher la page paramètres
  - `updateProfile()` - Mettre à jour le profil
  - `addVehicle()` - Ajouter un véhicule
  - `deleteVehicle()` - Supprimer un véhicule
  - `addFavorite()` - Ajouter un favori
  - `deleteFavorite()` - Supprimer un favori

#### `ParkingController.php` - Carte des parkings
- **Méthodes**:
  - `showMap()` - Afficher la carte interactive

---

### 🎨 VUES (`views/`)

Les vues gèrent **l'affichage** de l'interface utilisateur.

#### Structure des Vues
```
views/
├── layouts/base.php         # Template de base avec <html>, <head>, <body>
├── auth/login.php           # Formulaire de connexion
├── auth/register.php        # Formulaire d'inscription
├── user/settings.php        # Interface des paramètres utilisateur
└── parking/map.php          # Carte interactive Leaflet
```

#### Système de Layout
Toutes les vues utilisent le **template de base** (`layouts/base.php`) :
- `$pageTitle` - Titre de la page
- `$additionalHead` - CSS/JS supplémentaires
- `$content` - Contenu principal de la page

---

## 🔄 Flux de Fonctionnement

### Exemple : Connexion d'un utilisateur

1. **Point d'entrée** : `login.php`
   ```php
   require_once 'controllers/AuthController.php';
   $authController = new AuthController();
   ```

2. **Contrôleur** : `AuthController->login()`
   - Récupère les données POST
   - Valide les données
   - Appelle le modèle

3. **Modèle** : `User->findByIdentifier()`
   - Recherche l'utilisateur dans la BDD
   - Vérifie le mot de passe
   - Retourne les données

4. **Contrôleur** : Traite le résultat
   - Initialise la session si succès
   - Prépare le message d'erreur sinon

5. **Vue** : `views/auth/login.php`
   - Affiche le formulaire
   - Affiche les erreurs éventuelles
   - Utilise le layout de base

---

## 📊 Base de Données

### Tables Principales

- **Utilisateur** - Informations des utilisateurs
- **Vehicule** - Véhicules des utilisateurs
- **Favori** - Parkings favoris
- **Ref_Type_Vehicule** - Types de véhicules (voiture, moto, etc.)
- **Ref_Motorisation** - Motorisations (électrique, thermique, etc.)

### Configuration
Fichier : `config/db.php`
```php
$db_host = 'localhost';
$db_dbname = 'e40250u_sae301';
$db_username = 'root';
$db_password = '';
```

---

## ✨ Avantages de l'Architecture MVC

1. **Séparation des responsabilités** - Chaque composant a un rôle clair
2. **Réutilisabilité** - Les modèles sont utilisables partout
3. **Maintenabilité** - Modifications faciles et ciblées
4. **Testabilité** - Tests unitaires possibles
5. **Collaboration** - Plusieurs développeurs peuvent travailler en parallèle
6. **Évolutivité** - Ajout de fonctionnalités facilité
7. **Professionnalisme** - Standard de l'industrie

---

## 🚀 Utilisation

### Pages Principales
- **index.html** - Page d'accueil/portail
- **app.php** - Carte interactive des parkings
- **login.php** - Connexion utilisateur
- **register.php** - Inscription utilisateur
- **user_settings.php** - Gestion du profil/véhicules/favoris
- **logout.php** - Déconnexion

### Pour Développeurs

#### Ajouter un nouveau modèle
1. Créer `models/MonModele.php`
2. Étendre les méthodes CRUD de base
3. Utiliser `Database::getInstance()->getConnection()`

#### Ajouter un nouveau contrôleur
1. Créer `controllers/MonController.php`
2. Importer les modèles nécessaires
3. Créer les méthodes publiques

#### Ajouter une nouvelle vue
1. Créer `views/dossier/ma_vue.php`
2. Utiliser le système de layout avec `base.php`
3. Appeler depuis le contrôleur

---

## 📚 Documentation Supplémentaire

- **ARCHITECTURE_MVC.md** - Guide complet de l'architecture MVC (400+ lignes)
- **MVC_IMPLEMENTATION.md** - Résumé de l'implémentation
- **CHANGEMENTS.md** - Liste des modifications effectuées
- **README.md** - Documentation originale du projet

---

## 🔧 Technologies

- **Backend** : PHP 7+ avec PDO
- **Base de données** : MySQL
- **Frontend** : HTML5, CSS3, JavaScript
- **Cartographie** : Leaflet.js 1.9.4
- **Tuiles** : OpenStreetMap
- **Routing** : OSRM (Open Source Routing Machine)
- **Architecture** : MVC (Modèle-Vue-Contrôleur)

---

*Dernière mise à jour : 24 novembre 2025 - Migration complète vers architecture MVC*
