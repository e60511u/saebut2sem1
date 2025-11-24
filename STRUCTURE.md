# Structure du Projet - SAE Parking App

## 📁 Organisation des Fichiers

Le projet a été réorganisé selon les bonnes pratiques de développement web en séparant proprement les différents langages et responsabilités :

```
sae but 2 sem1/
├── assets/                  # Fichiers statiques (CSS, JS, images)
│   ├── css/                # Feuilles de style
│   │   ├── style.css       # Styles pour la carte principale (app.php)
│   │   ├── login.css       # Styles pour la page de connexion
│   │   ├── register.css    # Styles pour la page d'inscription
│   │   └── user_settings.css # Styles pour les paramètres utilisateur
│   └── js/                 # Scripts JavaScript
│       └── script.js       # Logique de la carte interactive
│
├── config/                 # Configuration de l'application
│   └── db.php             # Configuration et connexion à la base de données
│
├── includes/              # Logique métier PHP (fonctions réutilisables)
│   ├── auth.php          # Fonctions d'authentification (login, register, session)
│   └── user.php          # Fonctions de gestion utilisateur (CRUD véhicules, favoris)
│
├── app.php               # Page principale avec la carte interactive
├── login.php             # Page de connexion
├── register.php          # Page d'inscription
├── user_settings.php     # Page des paramètres utilisateur
├── logout.php            # Script de déconnexion
└── db.sql                # Script SQL de création de la base de données
```

## 🎯 Principes Appliqués

### 1. **Séparation des Préoccupations (Separation of Concerns)**
- **HTML** : Structure et contenu (dans les fichiers .php)
- **CSS** : Présentation et styles (dans `assets/css/`)
- **JavaScript** : Comportement dynamique (dans `assets/js/`)
- **PHP** : Logique métier et accès aux données (dans `includes/`)

### 2. **Réutilisabilité du Code**
Les fonctions communes ont été extraites dans des fichiers dédiés :

#### `includes/auth.php`
- `loginUser($identifier, $password)` - Authentification
- `registerUser($pseudo, $email, $password)` - Inscription
- `initUserSession($user)` - Initialisation de session
- `isLoggedIn()` - Vérification de connexion
- `requireLogin()` - Protection des pages

#### `includes/user.php`
- `getUserById($user_id)` - Récupération d'un utilisateur
- `updateUser(...)` - Mise à jour des infos utilisateur
- `getUserVehicles($user_id)` - Liste des véhicules
- `addVehicle(...)` / `deleteVehicle(...)` - Gestion des véhicules
- `getUserFavorites($user_id)` - Liste des favoris
- `addFavorite(...)` / `deleteFavorite(...)` - Gestion des favoris
- `getVehicleTypes()` / `getMotorisations()` - Données de référence

### 3. **Maintenabilité**
- Un fichier CSS par page facilite les modifications
- La logique métier centralisée évite la duplication
- Les chemins relatifs permettent la portabilité

### 4. **Sécurité**
- Séparation du code sensible (config DB) dans un dossier dédié
- Utilisation de fonctions pour éviter les injections SQL
- Validation centralisée des données

## 🔧 Utilisation

### Pages Publiques
- `login.php` - Connexion (redirige vers `app.php` si déjà connecté)
- `register.php` - Inscription (redirige vers `app.php` si déjà connecté)

### Pages Protégées (nécessitent une connexion)
- `app.php` - Carte interactive avec parkings
- `user_settings.php` - Gestion du profil, véhicules et favoris
- `logout.php` - Déconnexion

### Fichiers Inclus
Les fichiers `includes/` ne doivent **jamais** être appelés directement dans le navigateur. Ils sont chargés via `require_once` dans les pages PHP.

## 🎨 Personnalisation

### Modifier les Couleurs
Tous les styles utilisent une palette de couleurs cohérente :
- **Primaire** : `#8A0808` (rouge foncé)
- **Secondaire** : `#B71C1C` (rouge clair au survol)
- **Gris** : `#666`, `#333` pour le texte

Modifiez ces valeurs dans les fichiers CSS pour changer l'apparence globale.

### Ajouter une Nouvelle Page
1. Créer le fichier PHP à la racine
2. Créer le CSS correspondant dans `assets/css/`
3. Inclure les fichiers nécessaires : `includes/auth.php`, `includes/user.php`
4. Utiliser `requireLogin()` pour protéger la page si nécessaire

## 📝 Bonnes Pratiques Respectées

✅ **DRY** (Don't Repeat Yourself) - Pas de duplication de code  
✅ **Séparation HTML/CSS/JS/PHP** - Chaque langage dans son fichier  
✅ **Modularité** - Fonctions réutilisables et testables  
✅ **Nomenclature claire** - Noms de fichiers et fonctions explicites  
✅ **Architecture MVC-like** - Séparation vue/logique/données  
✅ **Sécurité** - Préparation des requêtes SQL, hashage des mots de passe

## 🚀 Prochaines Améliorations Possibles

- Créer un dossier `pages/` pour les vues PHP
- Ajouter un système de templates (header/footer communs)
- Implémenter un routeur pour des URLs propres
- Ajouter des tests unitaires pour les fonctions
- Créer un fichier de constantes pour les couleurs et config
