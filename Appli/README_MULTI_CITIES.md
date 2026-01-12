# 🏙️ Support Multi-Villes - Architecture

## 📋 Vue d'ensemble

L'application supporte maintenant plusieurs villes grâce à une architecture basée sur le **Design Pattern Strategy/Adapter**.

## 🏗️ Architecture

### Format JSON Standardisé

Tous les providers retournent un format uniforme :

```json
{
  "city": "metz",
  "parkings": [
    {
      "id": "unique_id",
      "name": "Nom du parking",
      "lat": 49.119,
      "lng": 6.176,
      "total_places": 100,
      "available_places": 45,
      "status": "OPEN|CLOSED|UNKNOWN",
      "cost": "Gratuit|Payant|...",
      "is_pmr": true|false,
      "has_electric_charging": true|false,
      "additional_info": {}
    }
  ],
  "count": 50
}
```

### Structure des Providers

```
providers/
├── ParkingProviderInterface.php    # Interface commune
├── MetzParkingProvider.php          # Adapter pour Metz (GeoJSON/WFS)
├── LondonParkingProvider.php        # Adapter pour Londres (REST TfL)
└── ParkingProviderFactory.php       # Factory pour créer les providers
```

### Configuration

- `config/cities.php` : Liste des villes supportées et leurs coordonnées
- `config/api_keys.php.example` : Template pour les clés API (à copier en `api_keys.php`)

## 🚀 Utilisation

### Ajouter une nouvelle ville

1. **Créer un nouveau Provider** :
   ```php
   class NewCityParkingProvider implements ParkingProviderInterface {
       // Implémenter getAllParkings() et searchParkings()
   }
   ```

2. **Ajouter dans la Factory** :
   ```php
   case 'newcity':
       return new NewCityParkingProvider();
   ```

3. **Ajouter dans `config/cities.php`** :
   ```php
   'newcity' => [
       'name' => 'Nouvelle Ville',
       'code' => 'newcity',
       'center' => ['lat' => ..., 'lng' => ...],
       'zoom' => 13,
       'provider' => 'NewCityParkingProvider'
   ]
   ```

4. **Ajouter dans la navbar** :
   ```html
   <option value="newcity">Nouvelle Ville</option>
   ```

## 🔑 Clés API

Pour Londres (TfL), obtenir une clé gratuite sur : https://api.tfl.gov.uk/

Copier `config/api_keys.php.example` en `config/api_keys.php` et remplir la clé.

**Note** : Le mode dégradé (données mockées) s'active automatiquement si l'API TfL échoue.

## 📝 Notes Techniques

- **SOLID** : Chaque provider respecte le SRP et l'OCP
- **Adapter Pattern** : Chaque provider adapte son format source au format standardisé
- **Factory Pattern** : Centralisation de la création des providers
- **Rétrocompatibilité** : Le frontend gère encore l'ancien format GeoJSON si nécessaire
