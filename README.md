# LGRSTOCK

Outil interne de gestion et de visualisation des stocks en réserve déportée, pour les magasins La Grande Récré de Tours Nord et Tours Centre.

---

## Description

Application web interne permettant de consulter, localiser et ajuster les produits stockés en réserve externe. L'objectif est de réduire le temps de recherche en centralisant l'état des stocks dans une interface simple, accessible depuis un PC ou une tablette.

Le stock est suivi **par dépôt** (Tours Nord et Tours Centre). Pour chaque produit, `products.quantite` est le **total**, maintenu automatiquement comme la somme des quantités de chaque dépôt (table `product_depot`).

---

## Fonctionnalités

### Espace employé (lecture seule)
- Recherche par code EAN (scan ou saisie), libellé ou fournisseur
- Redirection directe vers la fiche si un seul résultat, sinon liste paginée
- Fiche produit : libellé, EAN, fournisseur, prix, **stock par dépôt** (Nord / Centre) et indicateur de niveau

### Espace admin
- Gestion produits : recherche paginée, ajout, modification, suppression
- **Ajustement de stock par dépôt** : ajout ou retrait ponctuel sur le dépôt de l'admin connecté, avec plancher à 0
- Gestion utilisateurs : ajout, modification, suppression (protection anti-auto-suppression)
- Import / export CSV des produits (Excel-safe, upsert par EAN, validation en 2 passes)
- Import et consultation du référentiel produits « BOB »
- Vue d'ensemble : statistiques globales

---

## Stack technique

- **Frontend** — Vue 3 (Composition API, `<script setup>`), Vue Router, Vite
- **Backend** — PHP 8 natif (architecture MVC maison), PDO, authentification par sessions
- **Base de données** — MariaDB (compatible MySQL)
- **Outils** — XAMPP (développement local), Postman (tests API), Git / GitHub

---

## Architecture

Application trois tiers : le frontend Vue communique avec une API PHP (un fichier `.php` = une route) qui interroge la base via PDO. Toutes les requêtes transmettent le cookie de session (`credentials: 'include'`).

```
backend/
  api/            endpoints HTTP (Auth, Product, User, bob, export, import, stats)
  config/         cors.php, database.php (⚠️ non versionné)
  database/       schema.sql, seeds
  src/
    Controllers/  logique par ressource
    Middleware/   Auth.php (requireAuth, requireAdmin)
    Models/       accès aux données (Product, User, BobProduct)
frontend/
  src/
    components/   admin/ et employe/
    composables/  useAuth.js
    services/     appels API (productApi, authApi, userApi)
    views/        pages (Home, Admin, Login, ProductDetail…)
    router/
```

Le détail des tables (`products`, `product_depot`, `users`, `bob_products`, `login_attempts`) fait foi dans [`backend/database/schema.sql`](backend/database/schema.sql).

---

## Installation (développement local)

**Prérequis** : PHP 8+, MariaDB/MySQL, Node.js 20+, un serveur local type XAMPP.

**Backend**
1. Cloner le dépôt dans le répertoire `htdocs`
2. Créer la base et importer le schéma : `SOURCE backend/database/schema.sql;`
3. Copier la configuration et renseigner les identifiants :
   `cp backend/config/database.example.php backend/config/database.php`

**Frontend**
```bash
cd frontend
npm install
npm run dev      # développement — http://localhost:5173
npm run build    # build de production (dossier dist/)
```

---

## API

Endpoints REST sous `/api/`, un fichier `.php` par route. Les endpoints protégés passent par `requireAuth()` ou `requireAdmin()`. Les écritures se font en POST (`x-www-form-urlencoded`), les lectures en GET. Réponses en JSON de forme `{ error, message, data }`.

- **Auth** — `Auth/login.php`, `logout.php`, `check-auth.php`
- **Produits** — `Product/search.php`, `product.php`, `add-product.php`, `edit-product.php`, `delete-product.php`, `adjust-stock.php`
- **Utilisateurs** — `User/` (liste, ajout, modification, suppression)
- **Autres** — `stats.php`, `export.php`, `import.php`, `bob.php`, `bob_import.php`

---

## Authentification et rôles

Deux rôles, plus un magasin de rattachement (`tours_nord` / `tours_centre`) déterminant le dépôt sur lequel l'admin agit :
- **employé** — lecture seule (recherche + fiche produit)
- **admin** — accès complet (produits, utilisateurs, import/export, stats)

Les routes Vue sont protégées par des guards qui vérifient la session et le rôle avant chaque navigation.

---

## Sécurité

- Sessions PHP (`HttpOnly`, `SameSite=Strict`, cookie `Secure` en production, durée 8 h)
- Middleware `requireAuth` / `requireAdmin` sur les endpoints protégés
- Requêtes préparées via PDO (protection contre l'injection SQL)
- Mots de passe hachés (bcrypt)
- Limitation des tentatives de connexion (rate limiting)
- Guards Vue Router côté frontend

**Limites connues (usage interne)** : pas de jetons anti-CSRF dédiés — la protection repose sur le cookie `SameSite=Strict` ; les sessions actives ne sont pas invalidées à la suppression d'un utilisateur.

---

## Évolutions prévues

- Listes de transfert entre la réserve déportée et les magasins (anticipation des mouvements, historique par magasin) -> en développement.
- Export CSV filtré par fournisseur
