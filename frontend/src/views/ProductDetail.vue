<template>
  <main class="page">
    <div class="container">
      <!-- Bouton retour -->
      <nav class="back-button">
        <button @click="$router.push('/')" class="btn-back">
          ← Retour à la recherche
        </button>
      </nav>

      <!-- Message de chargement -->
      <MessageBox
        v-if="loading"
        type="loading"
        message="Chargement du produit..."
      />

      <!-- Message d'erreur -->
      <MessageBox v-if="error" type="error" :message="error" />

      <!-- fiche produit -->
      <article v-if="product" class="card-item">
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
            <span class="detail-value">{{ product.created_at }}</span>
          </div>

          <div class="detail-row">
            <span class="detail-label">Modifié le</span>
            <span class="detail-value">{{ product.updated_at }}</span>
          </div>
        </section>

        <!-- Prévision btn modifier quantité / btn ajouter à une liste -->
        <section class="product-actions">

          <button class="btn-action" @click="ouvrirModal">
            ✏️ Modifier la quantité
          </button>

          <button class="btn-action btn-delete" @click="supprimerProduit">
            🗑️ Supprimer le produit
          </button>

        </section>
      </article>
    </div>

    <Modal
      v-if="showModal"
      title="Modifier la quantité"
      @close="showModal = false"
    >
      <div class="form-quantite">
        <p class="product-name">{{ product.libelle }}</p>

        <p class="quantite-label">Nombre d'unités ({{ product.quantite }})</p>

        <div class="quantite-controls">
          <button class="btn-compteur" @click="limitDecrement">-</button>
          <span class="quantite-valeur">{{ nouvelleQuantite }}</span>
          <button class="btn-compteur" @click="nouvelleQuantite++">+</button>
        </div>

        <div class="form-actions">
          <button class="btn-annuler" @click="showModal = false">
            Annuler
          </button>
          <button class="btn-valider" @click="modifierQuantite">Valider</button>
        </div>
      </div>
    </Modal>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";

import { getProductById, updateStock, deleteProduct } from "@/services/api";

import MessageBox from "@/components/MessageBox.vue";
import Modal from "@/components/Modal.vue";

const route = useRoute();
const router = useRouter();

// Etats réactifs
const product = ref(null);
const loading = ref(false);
const error = ref(null);

// Modale et Modification quantite
const showModal = ref(false);
const nouvelleQuantite = ref(product.value?.quantite ?? 0);

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

function ouvrirModal() {
  nouvelleQuantite.value = product.value.quantite;
  showModal.value = true;
}

async function modifierQuantite() {
  try {
    await updateStock(product.value.id, nouvelleQuantite.value);
    // Mettre à jour l'affichage sans recharger la page
    product.value.quantite = nouvelleQuantite.value;
    showModal.value = false;
  } catch (err) {
    error.value = err.message;
  }
}

function limitDecrement() {
  if (nouvelleQuantite.value > 0) nouvelleQuantite.value--;
}

async function supprimerProduit() {
  // Confirmation avant suppression - Popup native du navigateur.
  if (!confirm(`Supprimer définitivement "${product.value.libelle}" ?`)) return;

  try {
    await deleteProduct(product.value.id);
    // produit supprimer -> retour HomePage
    router.push({
      path: '/',
      state: { deleted: true }
    });
  } catch (err) {
    error.value = err.message;
  }
}

// Hook de lifecycle : exécuté au montage du composant
onMounted(() => {
  fetchProduct();
});
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

/* Card principale */
.card-item {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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

.btn-delete {
  border-color: #fee2e2;
  color: #991b1b;
}

.btn-delete:hover {
  border-color: #ef4444;
  background: #fee2e2;
}

/* Modale Modif quantite */

.form-quantite {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-name {
  font-weight: 600;
  color: #6b7280;
  margin: 0;
}

.quantite-label {
  text-align: center;
  font-weight: 700;
}

.quantite-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fce4e4;
  border-radius: 12px;
  padding: 1.5rem;
}

.btn-compteur {
  width: 52px;
  height: 52px;
  border-radius: 10px;
  border: none;
  background: white;
  font-size: 1.5rem;
  font-weight: 600;
  cursor: pointer;
  color: #1f2937;
}

.btn-compteur:hover {
  background: #f3f4f6;
}

.quantite-valeur {
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
}

.btn-annuler {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: white;
  font-weight: 600;
  cursor: pointer;
}

.btn-valider {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 10px;
  background: #667eea;
  color: white;
  font-weight: 600;
  cursor: pointer;
}

.btn-valider:hover {
  background: #5a67d8;
}
</style>
