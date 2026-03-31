# LGR Stock

Outil interne de gestion et de visualisation des stocks, destiné aux équipes de La Grande Récré (Tours Centre et Tours Nord).

---

## Description

**LGR Stock** est une application web interne permettant de consulter et localiser rapidement les produits en réserve.
L'objectif est de réduire le temps de recherche en magasin en centralisant les informations de stock dans une interface simple et accessible depuis n'importe quel appareil.

---

## Contexte

Projet réalisé dans le cadre d'une alternance de 2ème année de Bachelor DWWM.
Il répond à un besoin réel de l'entreprise en proposant un outil adapté à un usage interne quotidien.

---

## Fonctionnalités actuelles

- Recherche de produits par code EAN (scan ou saisie manuelle), par libellé ou par fournisseur
- Affichage d'une liste de résultats (mini fiches produit) en cas de résultats multiples
- Redirection automatique vers la fiche produit si un seul résultat est trouvé
- Fiche produit avec :
  - Libellé
  - Code EAN
  - Fournisseur
  - Quantité disponible (indicateur coloré selon le niveau de stock)
  - Gestion des états de l'interface : chargement, erreur, aucun résultat
  - Modification de la quantite en stock via une modale
  - Suppression d'un produit avec confirmation
  


---

## Stack technique

### Frontend

| Technologie | Rôle |
|---|---|
| [Vue.js 3](https://vuejs.org/) (Composition API) | Framework JavaScript réactif |
| [Vue Router](https://router.vuejs.org/) | Navigation entre les vues |
| [Vite](https://vitejs.dev/) | Bundler et serveur de développement |

### Backend

| Technologie | Rôle |
|---|---|
| PHP (natif) | Serveur HTTP et logique métier |
| PDO | Accès sécurisé à la base de données |

### Base de données

| Technologie | Rôle |
|---|---|
| MySQL 8.0 | Stockage des données produits |

### Outils de développement

| Outil | Usage |
|---|---|
| XAMPP / Laragon | Environnement de développement local |
| Postman | Tests des endpoints API |
| Looping | Modélisation de la base de données (MCD/MLD) |
| npm / pnpm | Gestion des dépendances JavaScript |
| Git / GitHub | Versioning |

---

## Architecture

```
┌──────────────┐                    ┌──────────────┐
│   Vue.js 3   │  ←── HTTP/JSON ──→ │     PHP      │
│  (Frontend)  │                    │  (Backend)   │
└──────────────┘                    └──────┬───────┘
  Interface web                            │
  Recherche produits                       │
  Affichage du stock                       ▼
                                    ┌──────────────┐
                                    │    MySQL     │
                                    │  (Database)  │
                                    └──────────────┘
```

Le frontend Vue.js communique avec le backend PHP via une API REST.
Le backend expose des endpoints qui interrogent la base de données MySQL via PDO.

```
LGRSTOCK/
├── backend/
│   ├── api/            # Endpoints REST (search, product, import)
│   ├── config/         # Configuration base de données
│   ├── database/       # Schéma SQL et données de test
│   └── src/
│       ├── Controllers/ # Traitement des requêtes HTTP
│       ├── Models/      # Requêtes base de données
│       └── Services/    # Services métier (import CSV)
└── frontend/
    └── src/
        ├── components/  # Composants réutilisables
        ├── views/       # Pages de l'application
        ├── router/      # Configuration des routes
        └── services/    # Appels API centralisés
```

---

## Installation (temporaire)

### Prérequis

- PHP 7.4+
- MySQL 8.0
- Node.js + npm ou pnpm
- XAMPP / Laragon (ou tout autre serveur local)

### Backend

1. Cloner le dépôt dans le répertoire `htdocs` (XAMPP) ou `www` (Laragon)
2. Créer la base de données et importer le schéma :
   ```sql
   SOURCE backend/database/schema.sql;
   ```
3. Copier le fichier de configuration et renseigner les identifiants :
   ```bash
   cp backend/config/database.example.php backend/config/database.php
   ```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

L'application est accessible sur `http://localhost:5173` (dev) ou via le serveur local après build.

---

## API

| Méthode | Endpoint | Description |
|---|---|---|
| GET | `/api/search.php` | Recherche par EAN, libellé ou fournisseur |
| GET | `/api/product.php?id=` | Récupère un produit par son ID |
Paramètres de recherche : `?ean=`, `?libelle=`, `?fournisseur=` (combinables)
| POST | `/api/add-product.php` | Ajoute un nouveau produit |
| POST | `/api/update-stock.php` | Met à jour la quantité d'un produit |
| POST | `/api/delete-product.php` | Supprime un produit 

## Sécurité

⚠️ Ce projet tourne en réseau local interne. Le système d'authentification est en cours de développement — les endpoints API ne sont pas encore protégés par token.