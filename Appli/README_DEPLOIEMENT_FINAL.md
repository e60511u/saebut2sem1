# 📦 Guide de Déploiement Final - Application Parking Metz

## 🎯 Réponse à votre question

**OUI, vous avez raison !** 

Vous devez mettre **UNIQUEMENT le dossier `Appli/`** sur le serveur IUT.

**Pas besoin d'autres fichiers !**

---

## ✅ Ce qui va sur le serveur

### **UNIQUEMENT le dossier `Appli/`**

Tout est déjà dans `Appli/` :
- ✅ Tous les fichiers PHP
- ✅ Tous les fichiers JavaScript
- ✅ Tous les fichiers CSS
- ✅ Toute la structure MVC
- ✅ Tous les endpoints API

**Structure complète à uploader :**
```
Appli/
├── api/                    ← Endpoints API
├── assets/                ← CSS et JS
│   ├── css/
│   └── js/
├── config/                ← Configuration BDD
├── includes/              ← Header, footer, etc.
├── index.php              ← Point d'entrée
└── ... (fichiers de documentation)
```

---

## ❌ Ce qui NE va PAS sur le serveur

### **Le dossier `bdd/`**
- Le fichier `bdd/e40250u_sae301.sql` doit être **importé via phpMyAdmin**, pas uploadé
- C'est un script SQL, pas un fichier à mettre sur le serveur web

### **Le dossier `saebut2sem1-MVP/`**
- C'est l'ancien MVP, vous n'en avez plus besoin

### **Le fichier `Appli.zip`**
- C'est juste une archive, pas nécessaire sur le serveur

---

## 🔧 Configuration nécessaire

### 1. Modifier `config/db.php` sur le serveur

Une fois `Appli/` uploadé, modifiez `config/db.php` avec vos identifiants IUT :

```php
$host = 'devbdd.iutmetz.univ-lorraine.fr';
$dbname = 'e40250u_sae301';
$username = 'votre_identifiant_IUT';  // ⚠️ À remplacer
$password = 'votre_mot_de_passe_IUT'; // ⚠️ À remplacer
```

### 2. Importer la base de données

**IMPORTANT** : Le fichier SQL ne s'upload pas !

1. Connectez-vous à **phpMyAdmin** sur le serveur IUT
2. Sélectionnez votre base `e40250u_sae301`
3. Cliquez sur **"Importer"**
4. Choisissez le fichier `bdd/e40250u_sae301.sql` depuis votre ordinateur
5. Cliquez sur **"Exécuter"**

---

## 🚀 Étapes de déploiement

### Étape 1 : Uploader Appli/
```
1. Connectez-vous au serveur IUT via FTP
2. Créez un dossier (ex: parking-metz)
3. Uploadez TOUT le contenu de Appli/ dans ce dossier
```

### Étape 2 : Configurer la BDD
```
1. Modifiez config/db.php avec vos identifiants
2. Importez bdd/e40250u_sae301.sql via phpMyAdmin
```

### Étape 3 : Tester
```
1. http://votre_url/test_simple.php
2. http://votre_url/index.php
```

---

## 📋 Checklist rapide

- [ ] Uploader le dossier `Appli/` sur le serveur
- [ ] Modifier `config/db.php` avec vos identifiants IUT
- [ ] Importer `bdd/e40250u_sae301.sql` via phpMyAdmin
- [ ] Tester avec `test_simple.php`
- [ ] Tester l'application avec `index.php`

---

## ✨ Résumé

**Vous n'avez besoin QUE du dossier `Appli/` sur le serveur !**

Tout le reste (BDD, documentation, ancien MVP) reste sur votre ordinateur.

L'application respecte les principes SOLID :
- ✅ Séparation des responsabilités
- ✅ Code modulaire
- ✅ Architecture propre
- ✅ Tout est dans Appli/

---

## 📞 Besoin d'aide ?

Consultez :
- `DEPLOIEMENT_SERVEUR_IUT.md` pour le guide détaillé
- `README_DEPANNAGE.md` pour résoudre les problèmes
- `CHECKLIST_DEPLOIEMENT.txt` pour la checklist rapide
