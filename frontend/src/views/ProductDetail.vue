<template>
    <div class="page">
        <div class="container">

            <!-- Bouton retour -->
            <div class="back-button">
                <button @click="$router.back()" class="btn-back">
                    ← Retour à la recherche
                </button>
            </div>

            <!-- Message de chargement -->
            <div v-if="loading" class="message info">
                ⏳ Chargement du produit...
            </div>

            <!-- Message d'erreur -->
            <div v-if="error" class="message error">
                ❌ {{ error }}
            </div>

            <!-- fiche produit -->
            <div v-if="product" class="card-item">
                <div class="product-header">
                    <h1>{{ product.libelle }}</h1>
                    <span class="stock-badge" :class="stockClass">
                    {{ product.quantite }} unités
                    </span>
                </div>

                <div class="product-details">
                    <div class="detail-row">
                        <span class="detail-label">Code EAN</span>
                        <span class="detail-value">{{ product.ean }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Fournisseur</span>
                        <span class="detail-value">{{ product.fournisseur || 'Non renseigné'}}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Quantité en stock</span>
                        <span class="detail-value">{{ product.quantite }} unités</span>
                    </div>
                </div>

                <!-- Prévision btn modifier quantité / btn ajouter à une liste -->
                <div class="product-actions">
                    <button class="btn-action" disabled>
                        ✏️ Modifier la quantité
                    </button>
                    <button class="btn-action" disabled>
                        📋 Ajouter à une liste
                    </button>
                </div>

            </div>

        </div>
    </div>
</template>