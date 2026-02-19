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

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router';

const route = useRoute()
const router = useRouter()

// Etats réactifs 
const product = ref(null)
const loading = ref(false)
const error = ref(null)

// Computed : class CSS selon le stock 
const stockClass = computed(() => {
    if (stock === 0) return 'stock-empty'
    if (stock < 5) return 'stock-low'
    return 'stock-ok'
})

/**
 * Récupère les données du produit depuis l'API 
 */

async function fetchProduct() {
    const productId = route.params.id
    
    loading.value = true
    error.value = null

    const apiURL = `http://localhost/LGRSTOCK/backend/api/product.php?id=${productId}`

    console.log('Récupération du produit :', productId)

    try {
        const response = await fetch(apiURL)
        const data = await response.json()

        console.log('Réponse API:', data)

        if(data.error) {
            error.value = data.message
        } else {
            product.value =data.data
        }

    } catch (err) {
        console.error('Erreur :', err)
        error.value = 'Erreur lors du chargement du produit'
    } finally {
        loading.value = false
    }
}

// Hook de lifecycle : exécuté au montage du composant
onMounted(() => {
    fetchProduct()
})
</script>