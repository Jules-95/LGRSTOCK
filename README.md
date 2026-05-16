# LGRSTOCK

Outil interne de gestion et de visualisation des stocks, destiné aux équipes de La Grande Récré (Tours Centre et Tours Nord).

---

## Description

**LGRSTOCK** est une application web interne permettant de consulter, localiser et gérer les produits en réserve externe.
L'objectif est de réduire le temps de recherche en magasin en centralisant les informations de stock dans une interface simple et accessible depuis n'importe quel appareil (PC, tablette).

---

## Contexte

Projet réalisé dans le cadre d'une alternance de 2ème année de Bachelor DWWM.
Il répond à un besoin réel de l'entreprise en proposant un outil adapté à un usage interne quotidien.

---

## Fonctionnalités

### Espace employé
- Recherche de produits par code EAN (scan ou saisie manuelle), libellé ou fournisseur
- Redirection automatique vers la fiche produit si un seul résultat est trouvé
- Affichage d'une liste paginée en cas de résultats multiples
- Fiche produit avec libellé, EAN, fournisseur et indicateur coloré de niveau de stock

### Espace admin (dashboard)
- Vue d'ensemble : statistiques globales (produits, ruptures, utilisateurs, dernière mise à jour)
- Gestion produits : recherche paginée, ajout, modification, suppression
- Gestion utilisateurs : ajout, modification, suppression (protection anti-auto-suppression)
- Export CSV (Excel-safe, BOM UTF-8, EAN protégé contre la troncature)
- Import CSV (validation en 2 passes, mapping intelligent des colonnes, upsert par EAN)

---

## Stack technique

### Frontend

| Technologie | Rôle |
|---|---|
| Vue.js 3 (Composition API, `<script setup>`) | Framework JavaScript réactif |
| Vue Router | Navigation et guards de routes |
| Vite | Bundler et serveur de développement |

### Backend

| Technologie | Rôle |
|---|---|
| PHP 8 natif (architecture MVC maison) | Serveur HTTP et logique métier |
| PDO | Accès sécurisé à la base de données |
| Sessions PHP | Authentification (HttpOnly, SameSite=Lax, durée 8h) |

### Base de données

| Technologie | Rôle |
|---|---|
| MySQL 8.0 | Stockage des données produits et utilisateurs |

### Outils de développement

| Outil | Usage |
|---|---|
| XAMPP | Environnement de développement local |
| Postman | Tests des endpoints API |
| Looping | Modélisation de la base de données (MCD/MLD) |
| npm | Gestion des dépendances JavaScript |
| Git / GitHub | Versioning |

---

## Architecture

┌──────────────┐                    ┌──────────────┐
│   Vue.js 3   │  ←── HTTP/JSON ──→ │     PHP      │
│  (Frontend)  │                    │  (Backend)   │
└──────────────┘                    └──────┬───────┘
Interface web                            │
Recherche produits                       │
Gestion du stock                         ▼
┌──────────────┐
│    MySQL     │
│  (Database)  │
└──────────────┘

Le frontend Vue.js communique avec le backend PHP via une API REST.
Le backend expose des endpoints qui interrogent la base de données MySQL via PDO.
Toutes les requêtes `fetch()` utilisent `credentials: 'include'` pour transmettre le cookie de session.

### Arborescence

LGRSTOCK/
├── backend/
│   ├── api/
│   │   ├── Auth/         # login.php, logout.php, check-auth.php
│   │   ├── Product/      # search.php, product.php, add-product.php, edit-product.php, delete-product.php
│   │   ├── User/         # users.php, add-user.php, edit-user.php, delete-user.php
│   │   ├── export.php    # Export CSV
│   │   ├── import.php    # Import CSV
│   │   └── stats.php     # Statistiques dashboard
│   ├── config/
│   │   ├── cors.php
│   │   ├── database.example.php
│   │   └── database.php  # ⚠️ Ne pas committer (contient les credentials)
│   ├── database/
│   │   ├── schema.sql
│   │   ├── seed_products.sql
│   │   └── seed_users.sql
│   └── src/
│       ├── Controllers/  # AuthController, ProductController, UserController, ExportController, ImportController, StatsController
│       ├── Middleware/   # Auth.php — garde-barrière (requireAuth, requireAdmin)
│       └── Models/       # Product.php, User.php
└── frontend/
└── src/
├── assets/       # CSS global (main.css, admin.css, form.css)
├── components/
│   ├── admin/    # Sections du dashboard admin
│   └── employe/  # Layout et topbar espace employé
├── composables/  # useAuth.js — état d'authentification partagé entre composants
├── router/       # index.js (guards de routes)
├── services/     # productApi.js, authApi.js, userApi.js
└── views/        # HomeView, AdminView, LoginView, ProductDetail, NotFoundView

---

## Authentification et rôles

Deux rôles utilisateur :
- **employé** : accès en lecture seule (recherche + fiche produit)
- **admin** : accès complet (CRUD produits, gestion utilisateurs, import/export, stats)

Les sessions PHP durent 8 heures. Le cookie `PHPSESSID` est `HttpOnly` et `SameSite=Lax`.
Les routes Vue sont protégées par des guards qui vérifient la session et le rôle avant chaque navigation.

---

## Base de données

### Table `products`
| Colonne | Type | Description |
|---|---|---|
| id | INT AUTO_INCREMENT | Identifiant unique |
| ean | VARCHAR(13) UNIQUE | Code EAN produit |
| libelle | VARCHAR(255) | Nom du produit |
| fournisseur | VARCHAR(255) | Nom du fournisseur |
| ref_fournisseur | VARCHAR(255) | Référence fournisseur |
| prix | DECIMAL | Prix du produit |
| quantite | INT | Quantité en stock |
| created_at | DATETIME | Date de création |
| updated_at | DATETIME | Date de dernière modification |

### Table `users`
| Colonne | Type | Description |
|---|---|---|
| id | INT AUTO_INCREMENT | Identifiant unique |
| username | VARCHAR(255) UNIQUE | Nom d'utilisateur |
| password_hash | VARCHAR(255) | Mot de passe hashé (bcrypt) |
| role | ENUM('employe','admin') | Rôle de l'utilisateur |
| magasin | ENUM('tours_nord','tours_centre') | Magasin de rattachement |
| created_at | DATETIME | Date de création |

---

## Installation (développement local)

### Prérequis

- PHP 8+
- MySQL 8.0
- Node.js + npm
- XAMPP

### Backend

1. Cloner le dépôt dans le répertoire `htdocs`
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

L'application est accessible sur `http://localhost:5173` en développement.

---

## API

### Authentification
| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/Auth/login.php` | Non | Connexion |
| POST | `/api/Auth/logout.php` | Oui | Déconnexion |
| GET | `/api/Auth/check-auth.php` | Non | Vérification session |

### Produits
| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/Product/search.php` | Employé | Recherche paginée par EAN, libellé ou fournisseur |
| GET | `/api/Product/product.php?id=` | Employé | Récupère un produit par ID |
| POST | `/api/Product/add-product.php` | Admin | Ajoute un produit |
| POST | `/api/Product/edit-product.php` | Admin | Modifie un produit |
| POST | `/api/Product/delete-product.php` | Admin | Supprime un produit |

### Utilisateurs
| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/User/users.php` | Admin | Liste tous les utilisateurs |
| POST | `/api/User/add-user.php` | Admin | Ajoute un utilisateur |
| POST | `/api/User/edit-user.php` | Admin | Modifie un utilisateur |
| POST | `/api/User/delete-user.php` | Admin | Supprime un utilisateur |

### Autres
| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/stats.php` | Admin | Statistiques globales |
| GET | `/api/export.php` | Admin | Export CSV de tous les produits |
| POST | `/api/import.php` | Admin | Import CSV de produits |

---

## Sécurité

- Authentification par sessions PHP (HttpOnly, SameSite=Lax)
- Middleware `requireAuth()` et `requireAdmin()` sur tous les endpoints protégés
- Requêtes SQL préparées via PDO (protection injection SQL)
- Mots de passe hashés avec bcrypt
- Guards Vue Router côté frontend

**Limitations connues (usage interne uniquement) :**
- Pas de protection CSRF (SameSite=Lax uniquement)
- Pas de rate limiting
- Les sessions actives ne sont pas invalidées à la suppression d'un utilisateur

---

## Évolutions prévues (v2)

- Système de listes de transfert entre le stock déporté et les magasins (Tours Nord / Tours Centre)
- Export CSV filtré par fournisseur
- Amélioration de la gestion des utilisateurs
- Historique des transferts par magasin