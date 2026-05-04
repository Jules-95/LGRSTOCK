<template>
  <div>
    <div class="content-header">
      <h1 class="content-title">Recherche produit</h1>
    </div>

    <MessageBox v-if="successMessage" type="info" :message="successMessage" />

    <form @submit.prevent="handleSearch">
      <div class="search-fields">
        <div class="search-field">
          <label>Code EAN</label>
          <input
            v-model="filters.ean"
            type="text"
            placeholder="3700523456789"
            maxlength="13"
          />
        </div>
        <div class="search-field">
          <label>Libellé</label>
          <input
            v-model="filters.libelle"
            type="text"
            placeholder="Ex: Flip 7, Lego..."
          />
        </div>
        <div class="search-field">
          <label>Fournisseur</label>
          <input
            v-model="filters.fournisseur"
            type="text"
            placeholder="Ex: Mattel, Hasbro..."
          />
        </div>
        <button class="btn-search" type="submit">Rechercher</button>
      </div>
    </form>

    <div v-if="loading" class="state-message">Chargement...</div>
    <div v-else-if="errorMessage" class="state-message state-message--error">
      {{ errorMessage }}
    </div>
    <div v-else-if="searched && products.length === 0" class="state-message">
      Aucun produit trouvé.
    </div>

    <table v-else-if="products.length > 0" class="product-table">
      <thead>
        <tr>
          <th>Libellé</th>
          <th>EAN</th>
          <th>Fournisseur</th>
          <th>Quantité</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td>{{ product.libelle }}</td>
          <td class="td-ean">{{ product.ean }}</td>
          <td>{{ product.fournisseur || "—" }}</td>
          <td>{{ product.quantite }}</td>
          <td class="td-actions">
            <button class="btn-edit" @click="openEditModal(product)">
              Modifier
            </button>
            <button class="btn-delete" @click="confirmDelete(product)">
              Supprimer
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="state-message">
      Utilisez les barres de recherche pour trouver des produits.
    </div>

    <Modal
      v-if="showEditModal"
      title="Modifier le produit"
      @close="showEditModal = false"
    >
      <EditProductForm
        :product="selectedProduct"
        @success="handleEditSuccess"
        @cancel="showEditModal = false"
      />
    </Modal>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { searchProducts, deleteProduct } from "@/services/productApi";
import EditProductForm from "@/components/admin/EditProductForm.vue";
import Modal from "@/components/Modal.vue";
import MessageBox from "@/components/MessageBox.vue";

import '@/assets/admin.css'

const filters = ref({ ean: "", libelle: "", fournisseur: "" });
const products = ref([]);
const loading = ref(false);
const errorMessage = ref("");
const searched = ref(false);
const successMessage = ref("");
const showEditModal = ref(false);
const selectedProduct = ref(null);

async function handleSearch() {
  const { ean, libelle, fournisseur } = filters.value;

  if (!ean && !libelle && !fournisseur) {
    errorMessage.value = "Veuillez remplir au moins un champ de recherche";
    return;
  }

  loading.value = true;
  errorMessage.value = "";
  searched.value = true;
  products.value = [];

  try {
    const result = await searchProducts({ ean, libelle, fournisseur });
    products.value = result.data ?? [];
  } catch (err) {
    errorMessage.value = err.message;
  } finally {
    loading.value = false;
  }
}

function openEditModal(product) {
  selectedProduct.value = product;
  showEditModal.value = true;
}

function handleEditSuccess(updatedProduct) {
  const index = products.value.findIndex((p) => p.id === updatedProduct.id);
  if (index !== -1) {
    products.value[index] = updatedProduct;
  }
  showEditModal.value = false;
  successMessage.value = "Produit modifié avec succès";
  setTimeout(() => {
    successMessage.value = "";
  }, 3000);
}

async function confirmDelete(product) {
  if (!confirm(`Supprimer "${product.libelle}" ?`)) return;

  try {
    await deleteProduct(product.id);
    products.value = products.value.filter((p) => p.id !== product.id);
    successMessage.value = "Produit supprimé avec succès";
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (err) {
    errorMessage.value = err.message;
  }
}
</script>

<style scoped>


.search-fields {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  align-items: flex-end;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  flex: 1;
}

.search-field label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.search-field input {
  padding: 0.75rem 1rem;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-input);
  font-size: 0.9rem;
  background: white;
  outline: none;
}

.search-field input:focus {
  border-color: var(--color-primary);
}

.btn-search {
  padding: 0.75rem 1.5rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}

.btn-search:hover {
  background: var(--color-primary-dark);
}

.td-ean {
  font-family: monospace;
  font-size: 0.8rem;
  color: var(--color-text-light);
}

</style>
