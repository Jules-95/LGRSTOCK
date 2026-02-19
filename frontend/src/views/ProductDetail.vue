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
    if (!product.value) return ''

    const stock = product.value.quantite
    if (stock === 0) return 'stock-empty'
    if (stock < 10) return 'stock-low'
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

<style scoped>
.page {
  min-height: 100vh;
  padding: 2rem;
}

.container {
  max-width: 800px;
  margin: 0 auto;
}

/* Bouton retour */
.back-button {
  margin-bottom: 1.5rem;
}

.btn-back {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: #667eea;
  transform: translateX(-3px);
}

/* Messages d'état */
.message {
  padding: 1rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  font-weight: 500;
}

.message.info {
  background: #dbeafe;
  color: #1e40af;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
}

/* Card principale */
.card-item {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

/* Header avec titre et badge stock */
.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f3f4f6;
}

.product-header h1 {
  font-size: 1.75rem;
  color: #1f2937;
  margin: 0;
}

.stock-badge {
  padding: 0.5rem 1rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
}

.stock-badge.stock-ok {
  background: #d1fae5;
  color: #065f46;
}

.stock-badge.stock-low {
  background: #fef3c7;
  color: #92400e;
}

.stock-badge.stock-empty {
  background: #fee2e2;
  color: #991b1b;
}

/* Détails du produit */
.product-details {
  margin-bottom: 2rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: #6b7280;
}

.detail-value {
  color: #1f2937;
  font-weight: 500;
}

/* Actions */
.product-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-action {
  flex: 1;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:not(:disabled):hover {
  border-color: #667eea;
  background: #f9fafb;
}

.btn-action:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>