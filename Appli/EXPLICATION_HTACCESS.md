# 📄 À quoi sert le fichier .htaccess ?

## Rôle du .htaccess

Le fichier `.htaccess` est un fichier de configuration Apache qui permet de :
1. **Réécrire les URLs** (ex: transformer `/parking/123` en `/index.php?id=123`)
2. **Configurer Apache** (encodage, sécurité, etc.)
3. **Protéger des fichiers** (empêcher l'accès direct à certains fichiers)
4. **Définir des règles de sécurité** (headers HTTP, etc.)

## ❌ Avez-vous besoin du .htaccess pour votre application ?

### **NON, vous n'en avez PAS besoin !** ✅

Votre application fonctionne **parfaitement sans** `.htaccess` car :

1. ✅ **Pas de réécriture d'URL nécessaire**
   - Vous accédez directement à `index.php`
   - Les APIs sont dans `api/getParkings.php` (chemins directs)
   - Pas besoin de transformer les URLs

2. ✅ **Pas de protection spéciale nécessaire**
   - Les fichiers sensibles (`config/db.php`) sont déjà protégés par leur emplacement
   - Pas besoin de règles spéciales

3. ✅ **Configuration par défaut suffisante**
   - Apache gère déjà l'encodage UTF-8 par défaut
   - Les fichiers PHP sont déjà exécutés correctement

## 🎯 Recommandation

### **Supprimez complètement le fichier `.htaccess`** 

C'est la solution la plus simple et la plus sûre pour éviter les erreurs 500.

**Pourquoi ?**
- ✅ Votre application fonctionne sans
- ✅ Évite les problèmes de compatibilité avec le serveur IUT
- ✅ Moins de configuration = moins de risques d'erreur
- ✅ Le serveur IUT peut avoir des restrictions sur `.htaccess`

## 🔧 Comment supprimer le .htaccess ?

### Option 1 : Via FTP
1. Connectez-vous au serveur
2. Trouvez le fichier `.htaccess` dans votre dossier
3. **Supprimez-le** ou **renommez-le** en `.htaccess.old`

### Option 2 : Via ligne de commande (si vous avez SSH)
```bash
rm .htaccess
```
ou
```bash
mv .htaccess .htaccess.old
```

## ✅ Après suppression

Votre application devrait fonctionner normalement :
- ✅ `index.php` s'affichera correctement
- ✅ Les APIs fonctionneront (`api/getParkings.php`, etc.)
- ✅ Les fichiers CSS/JS se chargeront
- ✅ Tout fonctionnera comme avant

## 📝 Ce que fait le .htaccess dans votre cas

Dans votre fichier actuel, le `.htaccess` fait principalement :
- Définit l'encodage UTF-8 (déjà géré par défaut)
- Active mod_rewrite (pas utilisé dans votre app)
- Définit des headers de sécurité (optionnel)

**Rien de tout cela n'est strictement nécessaire** pour faire fonctionner votre application.

## 🚀 Conclusion

**Supprimez le `.htaccess` et testez votre application.** 

Si tout fonctionne (ce qui devrait être le cas), vous n'avez plus besoin de vous en préoccuper !

---

**Note** : Si plus tard vous avez besoin de fonctionnalités avancées (réécriture d'URL, protection de fichiers, etc.), vous pourrez toujours recréer un `.htaccess` adapté. Mais pour l'instant, vous n'en avez pas besoin.
