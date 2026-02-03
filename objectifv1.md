# LGR Stock 

Outil interne de gestion et de visualisation des réserves, destiné aux équipes de La Grande Récré (Tours centre et Tours Nord)

--- 

## Description 

**LGR Stock** est une application web interne permettant de consulter, localiser et mettre à jour les stocks de produits stockés sur le site externe de Colombe.  
L'objectif principal est de faciliter l'accès aux informations de stock afin d'optimiser le temps passé à la recherche produits. 

--- 

## Contexte du projet 

Ce projet est réalisé dans le cadre de mon alternance de 2ème année de Bachelor DWWM. 
Il répond à un besoin réel de l'entreprise en proposant un outil simple et adapté à un usage interne. 

--- 

## ⚙️ Fonctionnalités principales (V1)

- Recherche de produits par code EAN (scan ou saisie manuelle), par libellé ou par emplacement. 
- Affichage d'une liste (libellé + Quantité) en cas de résulat multiple avec sélection du produit à consulter.
- Affichage des informations produit : 
    - libellé
    - code EAN
    - quantité disponible 
    - emplacement (allée / étagère)
- Mise à jour manuelle des quantités 
- Visualisation d'une image d'emplacement (si disponible)

--- 

## 🛠️ Technologies utilisées

### **Frontend**
- **Vue.js 3** (composition API)
    - Framework Javascript progressif et réactif 
    - Simplicité d'apprentissage
    - Interface fluide et responsive sans rechargement de page

- **Vue Router**
    - Gestion de la navigation entre les différentes pages de l'application 

- **Axios**
    - Librairie pour effectuer les requêtes HTTP vers l'API backend
    - Gestion simplifiée des appels asynchrones. 

- **Tailwind CSS**
    - Framework CSS utility-first pour un développement rapide
    - Permet un design moderne et responsive sans CSS custom 
    - Optimisé pour la performance 

### **Backend** 
- **Symphony**

- **Laravel EXcel** (maatwebsite/excel)
    - Package pour l'import/export de fichiers CSC / Excel

- **Composer** 
    - Gestionnaire des dépendances PHP

### **Base de données**
- **MySQL 8.0**
    - Système de gestion de base de données relationnelle
    - Choisi pour les performances et l'apprentissage 
    - Supporte les accès multiples simultanés

### **Outils de développements**
-**XAMPP / Laragon**
    - Environnement de développement local

- **Postman** 
    - Tests et documentation de l'API REST

- **Git / Github** 
    - Pour le versionning

- **Looping**
    - Pour la modélisation de la BDD (MCD/MLD)

- **npm / pnpm**
    - Gestionnaire de paquet Javascript

---

## **Architecture**

```
┌──────────────┐                    ┌──────────────┐
│   Vue.js     │  ←── HTTP/JSON ──→ │   Laravel    │
│  (Frontend)  │                    │  (Backend)   │
└──────────────┘                    └──────┬───────┘
  Interface web                            │
  Recherche produits                       │
  Affichage + MAJ stock                    ▼
                                    ┌──────────────┐
                                    │    MySQL     │
                                    │  (Database)  │
                                    └──────────────┘
                                      Table products
```

### **Avantages de l'architecture**
- Séparation des responsabilités (frontend ≠ backend)
- API réutilisable (évolutive vers d'autres usages internes)
- Responsive (fonctionne sur mobile, tablette, desktop)
- Sécurisé (validation coté serveur, protection Laravel)

---

## Fonctionnalités secondaires avancées (V2)

**Feature "Changer de stock"**

Initialement prévu pour la reseve "Colombe", d'autres reserves pourraient être implantées.

**Feature liste utilisateur / action différées**

Fonctionnalité envisagée permettant de préparer des actions (ajouts/retraits) en amont en cas de contrainte de connexion, avec validation ultérieure.

---

## 🚀 Installation

