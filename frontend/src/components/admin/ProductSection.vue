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
          <th class="th-libelle">Libellé</th>
          <th class="th-ean">EAN</th>
          <th class="th-fournisseur">Fournisseur</th>
          <th class="th-ref">Réf. fourn</th>
          <th class="th-prix">Prix</th>
          <th class="th-qte">Qté</th>
          <th class="th-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td class="td-libelle">{{ product.libelle }}</td>
          <td class="td-ean">{{ product.ean }}</td>
          <td class="td-fournisseur">{{ product.fournisseur || "—" }}</td>
          <td class="td-secondary td-ref">{{ product.ref_fournisseur || "—" }}</td>
          <td class="td-secondary td-prix">
            {{ product.prix ? product.prix + " €" : "—" }}
          </td>
          <td class="td-qte">{{ product.quantite }}</td>


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

    <AppPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      @change="fetchPage"
    />

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
import AppPagination from "@/components/AppPagination.vue";

import "@/assets/admin.css";

const filters = ref({ ean: "", libelle: "", fournisseur: "" });
const products = ref([]);
const loading = ref(false);
const errorMessage = ref("");
const searched = ref(false);
const successMessage = ref("");
const showEditModal = ref(false);
const selectedProduct = ref(null);
const currentPage = ref(1);
const totalPages = ref(0);

async function handleSearch() {
  const { ean, libelle, fournisseur } = filters.value;

  if (!ean && !libelle && !fournisseur) {
    errorMessage.value = "Veuillez remplir au moins un champ de recherche";
    return;
  }

  currentPage.value = 1;
  await fetchPage(1);
}

async function fetchPage(page) {
  loading.value = true;
  errorMessage.value = "";
  searched.value = true;
  products.value = [];

  try {
    const result = await searchProducts(filters.value, page);
    products.value = result.data ?? [];
    currentPage.value = result.page;
    totalPages.value = Math.ceil(result.total / result.limit);
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
  transition: background 0.2s;
}

.btn-search:hover {
  background: var(--color-primary-dark);
}

.td-ean {
  font-family: monospace;
  font-size: 0.8rem;
  color: var(--color-text-light);
}

/* Correctifs de l'organisation du tableau */
/* Largeurs fixes par colonne */
.th-libelle,
.td-libelle {
  width: 25%;
}
.th-ean {
  width: 13%;
}
.th-fournisseur {
  width: 15%;
}
.th-ref {
  width: 10%;
}
.th-prix {
  width: 8%;
}
.th-qte {
  width: 6%;
}
.th-actions {
  width: 13%;
}

/* Empêche les retours à la ligne dans les headers */
th {
  white-space: nowrap;
  text-align: center;
}


/* Infos secondaires moins visibles */
.td-secondary {
  color: var(--color-text-light);
  font-size: 0.85rem;
}

.td-ean {
  text-align: center;
}

.td-fournisseur {
  text-align: center;
}

.td-ref {
  text-align: center;
}

.td-prix {
  text-align: center;
  font-variant-numeric: tabular-nums;
}

.td-qte {
  text-align: center;
  font-weight: 600;
}


.td-libelle {
  max-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

</style>
