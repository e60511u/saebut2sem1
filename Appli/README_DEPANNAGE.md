# 🔧 Guide de Dépannage - Erreur 500

## Problème : Internal Server Error (500)

Si vous obtenez une erreur 500, suivez ces étapes dans l'ordre :

### Solution 1 : Supprimer le fichier .htaccess (RECOMMANDÉ EN PREMIER)

Le fichier `.htaccess` peut causer des problèmes sur certains serveurs.

**Action :**
1. Connectez-vous au serveur via FTP
2. **Renommez ou supprimez** le fichier `.htaccess`
3. Réessayez d'accéder à l'application

L'application fonctionnera sans `.htaccess`, c'est juste pour optimiser.

### Solution 2 : Vérifier avec test_simple.php

1. Accédez à : `http://votre_url/test_simple.php`
2. Vérifiez quels tests échouent
3. Cela vous indiquera où est le problème

### Solution 3 : Vérifier les permissions des fichiers

Les fichiers doivent avoir les bonnes permissions :
- Fichiers PHP : `644` ou `755`
- Dossiers : `755`

### Solution 4 : Vérifier les logs d'erreur

Sur le serveur IUT, les logs sont généralement dans :
- `/var/log/apache2/error.log` ou
- `/var/log/httpd/error_log` ou
- Dans votre espace utilisateur

### Solution 5 : Vérifier la syntaxe PHP

Le code a été corrigé pour être compatible avec PHP 7.0+, mais vérifiez :
- Version PHP minimale : 7.0
- Extensions requises : `pdo`, `pdo_mysql`, `json`

### Solution 6 : Test minimal

Créez un fichier `test.php` avec juste :
```php
<?php
phpinfo();
?>
```

Si cela fonctionne, le problème vient du code de l'application.

## Checklist de vérification

- [ ] Le fichier `.htaccess` est supprimé ou renommé
- [ ] `test_simple.php` fonctionne
- [ ] Les fichiers sont bien uploadés
- [ ] Les permissions sont correctes
- [ ] La version PHP est 7.0+
- [ ] Les extensions PHP sont installées

## Si rien ne fonctionne

1. Testez avec un fichier PHP minimal (`<?php echo "test"; ?>`)
2. Vérifiez que vous êtes dans le bon répertoire
3. Contactez le support IUT avec les informations de `test_simple.php`
