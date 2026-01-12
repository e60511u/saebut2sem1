# 🔧 Corrections pour Metz - Problèmes Résolus

## ✅ Problèmes Corrigés

### 1. Les parkings n'ont plus d'infos (places disponibles/totales)
**Problème** : Les parkings de Metz n'affichaient plus les informations sur les places disponibles et totales.

**Cause** : Le provider Metz cherchait les propriétés `dispo` et `total`, mais l'API Metz utilise `place_libre` et `place_total` (ou `dispo` et `total` selon la source).

**Solution** :
- ✅ Correction de l'extraction dans `MetzParkingProvider::normalizeParking()`
- ✅ Priorité donnée à `place_libre` et `place_total` (API statique)
- ✅ Fallback sur `dispo` et `total` (API temps réel)
- ✅ Les informations de places sont maintenant correctement extraites et affichées

### 2. Le bouton "Masquer les parkings de rue" ne fonctionne pas
**Problème** : Le filtre ne fonctionnait pas avec le nouveau format standardisé.

**Cause** : Le filtre utilisait `isValidParking()` qui cherchait dans `properties` (format GeoJSON ancien), mais le nouveau format standardisé n'a plus cette structure.

**Solution** :
- ✅ Création de `isValidParkingStandardized()` pour le nouveau format
- ✅ Détection améliorée des vrais parkings :
  - Vérification du nom (exclut "undefined")
  - Vérification des informations de places (total > 5)
  - Détection de mots-clés (parking, p+r, république, saint-jacques, etc.)
  - Vérification du type dans `additional_info`
- ✅ Application du filtre dans `createParkingMarker()` pour les deux formats
- ✅ Rafraîchissement des marqueurs lors du clic sur le bouton

## 📝 Fichiers Modifiés

1. **Appli/providers/MetzParkingProvider.php**
   - Correction de l'extraction des places (`place_libre`/`place_total` en priorité)
   - Support des deux formats de propriétés (API temps réel et statique)

2. **Appli/assets/js/map.js**
   - Création de `isValidParkingStandardized()` pour le format standardisé
   - Amélioration de la détection des vrais parkings
   - Application du filtre dans `createParkingMarker()`

3. **Appli/assets/js/app.js**
   - Amélioration du rafraîchissement lors du clic sur le bouton filtre
   - Vider les marqueurs avant de réafficher avec le filtre

## 🧪 Test

Pour tester :
1. Recharger la page sur Metz
2. Vérifier que les parkings affichent les places disponibles/totales dans les popups
3. Cliquer sur "Masquer les parkings de rue"
4. Vérifier que seuls les vrais parkings (République, Saint-Jacques, etc.) sont affichés
5. Cliquer à nouveau pour réafficher tous les parkings

## ⚠️ Notes

- Le filtre fonctionne maintenant avec le format standardisé
- Les informations de places sont correctement extraites des deux APIs (temps réel et statique)
- Le filtre détecte les vrais parkings en se basant sur plusieurs critères (nom, places, mots-clés)
