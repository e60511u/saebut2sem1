# 🚀 Déploiement sur le Serveur IUT - Guide Simple

## ✅ CE QUE VOUS DEVEZ FAIRE

### **UNIQUEMENT le dossier `Appli/` sur le serveur !**

C'est tout ce dont vous avez besoin. Pas besoin d'autres fichiers.

---

## 📦 ÉTAPE 1 : Uploader le dossier Appli

1. **Connectez-vous au serveur IUT** via FTP/SFTP
2. **Créez un dossier** (ex: `parking-metz` ou `sae301`)
3. **Uploadez TOUT le contenu du dossier `Appli/`** dans ce dossier

```
Structure sur le serveur :
/votre_dossier/
├── api/
│   ├── getParkings.php
│   ├── getRoute.php
│   └── getUserPreferences.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       ├── api_client.js
│       ├── map.js
│       └── app.js
├── config/
│   ├── db.php
│   └── config.example.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── index.php
├── README.md
└── ... (autres fichiers de documentation)
```

---

## 🔧 ÉTAPE 2 : Configurer la base de données

### 2.1 Modifier config/db.php

Sur le serveur, ouvrez `config/db.php` et modifiez les lignes 49-52 :

```php
$host = 'devbdd.iutmetz.univ-lorraine.fr';
$dbname = 'e40250u_sae301';
$username = 'VOTRE_IDENTIFIANT_IUT';  // ⚠️ À remplacer
$password = 'VOTRE_MOT_DE_PASSE_IUT'; // ⚠️ À remplacer
```

### 2.2 Importer la base de données

**IMPORTANT** : Le fichier SQL (`bdd/e40250u_sae301.sql`) ne doit PAS être uploadé sur le serveur !

**À faire** :
1. Connectez-vous à **phpMyAdmin** sur le serveur IUT
2. Sélectionnez votre base de données `e40250u_sae301`
3. Cliquez sur l'onglet **"Importer"**
4. Choisissez le fichier `bdd/e40250u_sae301.sql` depuis votre ordinateur
5. Cliquez sur **"Exécuter"**

**C'est tout !** La base de données est maintenant créée.

---

## 🌐 ÉTAPE 3 : Accéder à l'application

**URL à utiliser** :
```
http://devbdd.iutmetz.univ-lorraine.fr/votre_dossier/
```

**Exemple** : Si votre dossier s'appelle `parking-metz` :
```
http://devbdd.iutmetz.univ-lorraine.fr/parking-metz/
```

---

## ✅ ÉTAPE 4 : Vérifier que tout fonctionne

1. **Test de configuration** :
   ```
   http://votre_url/test_simple.php
   ```
   Tous les tests doivent être verts ✓

2. **Test de l'API** :
   ```
   http://votre_url/api/getParkings.php
   ```
   Vous devriez voir du JSON avec les parkings

3. **Application complète** :
   ```
   http://votre_url/index.php
   ```
   La carte doit s'afficher avec les parkings

---

## 📋 RÉCAPITULATIF

### ✅ À uploader sur le serveur :
- **UNIQUEMENT le dossier `Appli/`** (tout son contenu)

### ❌ À NE PAS uploader :
- Le dossier `bdd/` (le fichier SQL doit être importé via phpMyAdmin)
- Le dossier `saebut2sem1-MVP/` (ancien MVP, pas nécessaire)
- Le fichier `Appli.zip` (c'est juste une archive)

### 🔧 À configurer :
- `config/db.php` avec vos identifiants IUT
- Importer `bdd/e40250u_sae301.sql` via phpMyAdmin

---

## 🆘 En cas de problème

1. **Erreur 500** : Supprimez le fichier `.htaccess` (voir `README_DEPANNAGE.md`)
2. **Erreur BDD** : Vérifiez `config/db.php` et que la base est importée
3. **Carte vide** : Ouvrez la console (F12) et vérifiez les erreurs JS

---

## ✨ C'est tout !

Une fois ces étapes faites, votre application est déployée et fonctionnelle.

**Rappel** : Vous n'avez besoin QUE du dossier `Appli/` sur le serveur. Tout le reste (BDD, documentation) reste sur votre ordinateur.
