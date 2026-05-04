<template>
  <PageLayout back-label="← Retour à la recherche">
    <!-- Message de chargement -->
    <MessageBox
      v-if="loading"
      type="loading"
      message="Chargement du produit..."
    />

    <!-- Message d'erreur -->
    <MessageBox v-if="error" type="error" :message="error" />

    <!-- fiche produit -->
    <AppCard v-if="product">
      <header class="product-header">
        <h1>{{ product.libelle }}</h1>
        <span class="stock-badge" :class="stockClass">
          {{ product.quantite }} unités
        </span>
      </header>

      <section class="product-details">
        <div class="detail-row">
          <span class="detail-label">Code EAN</span>
          <span class="detail-value">{{ product.ean }}</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Fournisseur</span>
          <span class="detail-value">{{
            product.fournisseur || "Non renseigné"
          }}</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Quantité en stock</span>
          <span class="detail-value">{{ product.quantite }} unités</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Créé le</span>
          <span class="detail-value">{{ formatDate(product.created_at) }}</span>
        </div>

        <div class="detail-row">
          <span class="detail-label">Modifié le</span>
          <span class="detail-value">{{ formatDate(product.updated_at) }}</span>
        </div>
      </section>

      <!-- Prévision btn modifier quantité / btn ajouter à une liste -->
      <section class="product-actions">
        <button class="btn-list" disabled>
          + Ajouter à une liste pour transfert
        </button>
      </section>
    </AppCard>

    <!-- Modale à venir pour le système de liste -->

  </PageLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";

import { getProductById } from "@/services/productApi";

import MessageBox from "@/components/MessageBox.vue";
import PageLayout from "@/components/employe/LayoutEmploye.vue";
import AppCard from "@/components/AppCard.vue";

const route = useRoute(); // Donne accès aux infos de la route actuelle -> Où je suis 


// Etats réactifs
const product = ref(null);
const loading = ref(false);
const error = ref(null);


// Computed : class CSS selon le stock
const stockClass = computed(() => {
  if (!product.value) return "";

  const stock = product.value.quantite;
  if (stock === 0) return "stock-empty";
  if (stock < 10) return "stock-low";
  return "stock-ok";
});

/**
 * Récupère les données du produit depuis l'API
 */

async function fetchProduct() {
  const productId = route.params.id;

  loading.value = true;
  error.value = null;

  try {
    product.value = await getProductById(productId);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}


/**
 * Formate une date SQL (ex: "2026-03-18 15:51:10") en français lisible
 * Résultat : "18/03/2026 à 15:51"
 */
function formatDate(dateStr) {
  if (!dateStr) return "-";
  const date = new Date(dateStr);
  return (
    date.toLocaleDateString("fr-FR") +
    " à " +
    date.toLocaleTimeString("fr-FR", { hour: "2-digit", minute: "2-digit" })
  );
}

// Hook de lifecycle : exécuté au montage du composant
onMounted(() => {
  fetchProduct();
});
</script>

<style scoped>
/* Header avec titre et badge stock */
.product-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid var(--color-bg-light);
}

.product-header h1 {
  font-size: 1.75rem;
  color: var(--color-text-dark);
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
  border-bottom: 1px solid var(--color-bg-light);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: var(--color-text-light);
}

.detail-value {
  color: var(--color-text-dark);
  font-weight: 500;
}

/* Actions */
.product-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-list {
  width: 100%;
  padding: 1rem;
  border: 2px dashed var(--color-border);
  border-radius: var(--radius-btn);
  background: var(--color-bg-soft);
  color: var(--color-text-light);
  font-weight: 600;
  cursor: not-allowed;
  font-size: 0.95rem;
}

</style>
