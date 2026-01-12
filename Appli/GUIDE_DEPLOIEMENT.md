# Guide de Déploiement - Application Parking Metz

## 📍 Table des matières
1. [Déploiement sur le serveur IUT](#déploiement-sur-le-serveur-iut)
2. [Test en local](#test-en-local)
3. [Configuration de la base de données](#configuration-de-la-base-de-données)
4. [Vérification et dépannage](#vérification-et-dépannage)

---

## 🖥️ Déploiement sur le serveur IUT

### Étape 1 : Transférer les fichiers

#### Option A : Via FTP/SFTP (FileZilla, WinSCP, etc.)
1. **Connectez-vous au serveur IUT** avec vos identifiants
   - Hôte : `devbdd.iutmetz.univ-lorraine.fr` (ou l'adresse fournie par l'IUT)
   - Protocole : FTP ou SFTP
   - Port : 21 (FTP) ou 22 (SFTP)

2. **Créez un dossier** sur le serveur (ex: `parking-metz` ou `sae301`)

3. **Uploadez TOUT le contenu du dossier `Appli/`** dans ce dossier
   ```
   Structure sur le serveur :
   /votre_dossier/
   ├── api/
   ├── assets/
   ├── config/
   ├── includes/
   ├── index.php
   ├── .htaccess
   └── ...
   ```

#### Option B : Via Git (si disponible)
```bash
git clone votre_repo
cd votre_repo/Appli
# Puis suivez les instructions de l'IUT pour déployer
```

### Étape 2 : Configuration de la base de données

1. **Modifiez le fichier `config/db.php`** sur le serveur avec vos identifiants réels :
   ```php
   $host = 'devbdd.iutmetz.univ-lorraine.fr';
   $dbname = 'e40250u_sae301';
   $username = 'votre_identifiant_IUT';  // ⚠️ À remplacer
   $password = 'votre_mot_de_passe_IUT'; // ⚠️ À remplacer
   ```

2. **Vérifiez que la base de données est créée** :
   - Connectez-vous à phpMyAdmin ou à votre interface MySQL
   - Importez le fichier `bdd/e40250u_sae301.sql` si ce n'est pas déjà fait

### Étape 3 : Accéder à l'application

**URL à utiliser dans le navigateur :**
```
http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/
```
ou
```
https://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/
```

**Exemple concret :**
Si vous avez créé le dossier `parking-metz`, l'URL sera :
```
http://devbdd.iutmetz.univ-lorraine.fr/parking-metz/
```

### Étape 4 : Vérification

1. **Testez la configuration** :
   ```
   http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/test_config.php
   ```
   Tous les tests doivent être verts ✓

2. **Testez l'API** :
   ```
   http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/api/getParkings.php
   ```
   Vous devriez voir du JSON avec les parkings

3. **Ouvrez l'application** :
   ```
   http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/index.php
   ```

---

## 💻 Test en local

### Option 1 : XAMPP (Windows) - RECOMMANDÉ

#### Installation
1. **Téléchargez XAMPP** : https://www.apachefriends.org/
2. **Installez XAMPP** dans `C:\xampp\`
3. **Démarrez Apache** depuis le panneau de contrôle XAMPP

#### Déploiement
1. **Copiez le dossier `Appli`** dans :
   ```
   C:\xampp\htdocs\Appli
   ```

2. **Configurez la base de données** :
   - Ouvrez phpMyAdmin : http://localhost/phpmyadmin
   - Créez une base de données `e40250u_sae301`
   - Importez le fichier `bdd/e40250u_sae301.sql`

3. **Modifiez `config/db.php`** :
   ```php
   $host = 'localhost';
   $dbname = 'e40250u_sae301';
   $username = 'root';      // Par défaut dans XAMPP
   $password = '';          // Par défaut vide dans XAMPP
   ```

4. **Accédez à l'application** :
   ```
   http://localhost/Appli/
   ```

#### Test de configuration
```
http://localhost/Appli/test_config.php
```

---

### Option 2 : WAMP (Windows)

#### Installation
1. **Téléchargez WAMP** : https://www.wampserver.com/
2. **Installez WAMP** dans `C:\wamp64\`
3. **Démarrez WAMP** (icône dans la barre des tâches → Démarrer tous les services)

#### Déploiement
1. **Copiez le dossier `Appli`** dans :
   ```
   C:\wamp64\www\Appli
   ```

2. **Suivez les mêmes étapes** que pour XAMPP (base de données, configuration)

3. **Accédez à l'application** :
   ```
   http://localhost/Appli/
   ```

---

### Option 3 : Serveur PHP intégré (pour tests rapides)

#### Utilisation
1. **Ouvrez un terminal** dans le dossier `Appli`
2. **Lancez le serveur PHP** :
   ```bash
   php -S localhost:8000
   ```
   ou
   ```powershell
   php -S localhost:8000
   ```

3. **Accédez à l'application** :
   ```
   http://localhost:8000/
   ```

⚠️ **Note** : Cette méthode ne fonctionne pas avec MySQL. Utilisez-la uniquement pour tester l'interface sans base de données.

---

## 🔧 Configuration de la base de données

### Sur le serveur IUT

1. **Identifiants à utiliser** :
   - Hôte : `devbdd.iutmetz.univ-lorraine.fr`
   - Base : `e40250u_sae301`
   - Utilisateur : Votre identifiant IUT (ex: `e40250u`)
   - Mot de passe : Votre mot de passe IUT

2. **Modifiez `config/db.php`** :
   ```php
   $host = 'devbdd.iutmetz.univ-lorraine.fr';
   $dbname = 'e40250u_sae301';
   $username = 'e40250u';  // ⚠️ Votre identifiant
   $password = 'votre_mdp'; // ⚠️ Votre mot de passe
   ```

### En local (XAMPP/WAMP)

1. **Identifiants par défaut** :
   - Hôte : `localhost`
   - Base : `e40250u_sae301` (à créer)
   - Utilisateur : `root`
   - Mot de passe : `` (vide)

2. **Modifiez `config/db.php`** :
   ```php
   $host = 'localhost';
   $dbname = 'e40250u_sae301';
   $username = 'root';
   $password = '';
   ```

### Import de la base de données

#### Via phpMyAdmin
1. Ouvrez phpMyAdmin (sur serveur IUT ou localhost)
2. Sélectionnez votre base de données
3. Cliquez sur l'onglet "Importer"
4. Choisissez le fichier `bdd/e40250u_sae301.sql`
5. Cliquez sur "Exécuter"

#### Via ligne de commande
```bash
mysql -u votre_utilisateur -p e40250u_sae301 < bdd/e40250u_sae301.sql
```

---

## ✅ Vérification et dépannage

### Checklist de vérification

- [ ] Tous les fichiers sont uploadés sur le serveur
- [ ] Le fichier `config/db.php` est configuré avec les bons identifiants
- [ ] La base de données est créée et importée
- [ ] Le serveur web (Apache) est démarré
- [ ] PHP est installé et fonctionnel
- [ ] Les extensions PHP nécessaires sont activées (PDO, pdo_mysql, json, mbstring)

### Tests à effectuer

1. **Test de configuration** :
   ```
   http://votre_url/test_config.php
   ```
   Tous les tests doivent être verts ✓

2. **Test de l'API Parkings** :
   ```
   http://votre_url/api/getParkings.php
   ```
   Doit retourner du JSON avec les parkings

3. **Test de l'API Route** :
   ```
   http://votre_url/api/getRoute.php?lat1=49.1193&lng1=6.1757&lat2=49.1200&lng2=6.1760
   ```
   Doit retourner un itinéraire en JSON

4. **Test de l'API Préférences** :
   ```
   http://votre_url/api/getUserPreferences.php?user_id=1
   ```
   Doit retourner les préférences de l'utilisateur 1

5. **Test de l'application complète** :
   ```
   http://votre_url/index.php
   ```
   La carte doit s'afficher avec les parkings

### Problèmes courants et solutions

#### ❌ Erreur 404 - Page non trouvée
**Cause** : Mauvais chemin ou fichiers manquants
**Solution** :
- Vérifiez que tous les fichiers sont uploadés
- Vérifiez l'URL dans le navigateur
- Vérifiez le fichier `.htaccess`

#### ❌ Erreur de connexion à la base de données
**Cause** : Mauvais identifiants ou serveur MySQL non accessible
**Solution** :
- Vérifiez les identifiants dans `config/db.php`
- Vérifiez que MySQL est démarré (en local)
- Testez la connexion avec phpMyAdmin

#### ❌ La carte ne s'affiche pas
**Cause** : Problème de chargement JavaScript ou Leaflet
**Solution** :
- Ouvrez la console du navigateur (F12) et vérifiez les erreurs
- Vérifiez que les fichiers JS sont bien chargés
- Vérifiez la connexion Internet (Leaflet est chargé depuis CDN)

#### ❌ Les parkings ne s'affichent pas
**Cause** : Problème avec l'API Open Data Metz ou CORS
**Solution** :
- Vérifiez la console du navigateur pour les erreurs
- Testez l'API directement : `api/getParkings.php`
- Vérifiez que l'API Open Data Metz est accessible

#### ❌ La géolocalisation ne fonctionne pas
**Cause** : Site non en HTTPS ou permissions refusées
**Solution** :
- Autorisez la géolocalisation dans les paramètres du navigateur
- En local, utilisez `http://localhost` (fonctionne en local)
- Sur le serveur, utilisez HTTPS si possible

#### ❌ Erreur 500 - Erreur serveur
**Cause** : Erreur PHP
**Solution** :
- Vérifiez les logs d'erreur PHP
- Activez temporairement `display_errors` dans `config/db.php`
- Vérifiez la syntaxe PHP avec `php -l nom_fichier.php`

---

## 🔒 Sécurité - À faire avant la mise en production

1. **Supprimez `test_config.php`** (contient des informations sensibles)
2. **Désactivez l'affichage des erreurs** dans `.htaccess`
3. **Protégez `config/db.php`** avec des permissions restrictives
4. **Utilisez HTTPS** sur le serveur de production
5. **Validez et échappez** toutes les entrées utilisateur (déjà fait dans le code)

---

## 📞 Support

Si vous rencontrez des problèmes :
1. Vérifiez la console du navigateur (F12)
2. Vérifiez les logs PHP du serveur
3. Testez chaque endpoint API individuellement
4. Utilisez `test_config.php` pour diagnostiquer

---

## 📝 Résumé rapide

### Sur le serveur IUT :
```
1. Uploader tous les fichiers dans un dossier sur le serveur
2. Configurer config/db.php avec vos identifiants IUT
3. Accéder à : http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/
```

### En local (XAMPP) :
```
1. Copier Appli dans C:\xampp\htdocs\
2. Configurer config/db.php (localhost, root, '')
3. Créer la base de données et importer le SQL
4. Accéder à : http://localhost/Appli/
```

Bon déploiement ! 🚀
