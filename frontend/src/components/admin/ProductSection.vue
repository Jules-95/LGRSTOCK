<template>
  <div>
    <div class="content-header">
      <h1 class="content-title">Recherche produit</h1>
    </div>

    <MessageBox v-if="successMessage" type="info" :message="successMessage" />

    <form @submit.prevent="handleSearch">
      <div class="search-fields">
        <div class="search-field">
          <label for="search-ean">Code EAN</label>
          <input
            id="search-ean"
            name="ean"
            v-model="filters.ean"
            type="text"
            placeholder="3700523456789"
            maxlength="13"
            @focus="$event.target.select()"
          />
        </div>
        <div class="search-field">
          <label for="search-libelle">Libellé</label>
          <input
            id="search-libelle"
            name="libelle"
            v-model="filters.libelle"
            type="text"
            placeholder="Ex: Flip 7, Lego..."
          />
        </div>
        <div class="search-field">
          <label for="search-fournisseur">Fournisseur</label>
          <input
            id="search-fournisseur"
            name="fournisseur"
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
          <th class="th-qte">Qté Nord</th>
          <th class="th-qte">Qté Centre</th>
          <th class="th-qte">Total</th>
          <th class="th-actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in products" :key="product.id">
          <td class="td-libelle" data-label="Libellé">{{ product.libelle }}</td>
          <td class="td-ean" data-label="EAN">{{ product.ean }}</td>
          <td class="td-fournisseur" data-label="Fournisseur">
            {{ product.fournisseur || "—" }}
          </td>
          <td class="td-secondary td-ref" data-label="Réf. fourn">
            {{ product.ref_fournisseur || "—" }}
          </td>
          <td class="td-secondary td-prix" data-label="Prix">
            {{ product.prix ? product.prix + " €" : "—" }}
          </td>
          <td class="td-qte" data-label="Nord">{{ product.qte_nord ?? 0 }}</td>
          <td class="td-qte" data-label="Centre">{{ product.qte_centre ?? 0 }}</td>
          <td class="td-qte" data-label="Total">{{ product.quantite }}</td>

          <td class="td-actions" data-label="">
            <div
              class="dropdown"
              :class="{ open: openDropdown === product.id }"
            >
              <button class="btn-dots" @click.stop="toggleDropdown(product.id)">
                ···
              </button>
              <div class="dropdown-menu">
                <button
                  @click="
                    openEditModal(product);
                    openDropdown = null;
                  "
                >
                  Modifier
                </button>
                <button
                  class="danger"
                  @click="
                    confirmDelete(product);
                    openDropdown = null;
                  "
                >
                  Supprimer
                </button>
              </div>
            </div>
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
import { ref, onMounted } from "vue";
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

const openDropdown = ref(null);

onMounted(() => {
  document.addEventListener("click", () => {
    openDropdown.value = null;
  });
});

function toggleDropdown(id) {
  openDropdown.value = openDropdown.value === id ? null : id;
}

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
  window.scrollTo({ top: 0, behavior: "smooth" });
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
    window.scrollTo({ top: 0, behavior: "smooth" });
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (err) {
    errorMessage.value = err.message;
  }
}
</script>

<style scoped>
/* ── Formulaire de recherche ── */
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

/* ── Colonnes du tableau produits ── */
th {
  white-space: nowrap;
  text-align: center;
}

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

/* ── Styles spécifiques aux cellules ── */
.td-libelle {
  max-width: none;
  overflow: visible;
  white-space: normal;
}

.td-ean {
  font-family: monospace;
  font-size: 0.8rem;
  color: var(--color-text-light);
  text-align: center;
}

.td-secondary {
  color: var(--color-text-light);
  font-size: 0.85rem;
}

.td-fournisseur,
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

.product-table tbody tr:nth-child(even) {
  background: var(--color-bg-soft);
}

@media (max-width: 1000px) {
  form {
    display: flex;
    justify-content: center;
  }

  .search-fields {
    flex-direction: column;
    max-width: 100%;
    margin-top: 1.5rem;
    align-items: stretch;
  }

  .btn-search {
    width: 100%;
  }

  .product-table {
    background: transparent;
    border: none;
    box-shadow: none;
  }

  .product-table thead {
    display: none;
  }

  .product-table tr {
    display: block;
    background: white;
    border-radius: var(--radius-card);
    margin-bottom: 0.75rem;
    padding: 0.75rem 1rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
    border-bottom: none;
  }

  .product-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--color-border);
    font-size: 0.85rem;
    text-align: right;
  }

  .product-table td:last-child {
    border-bottom: none;
  }

  .product-table td::before {
    content: attr(data-label);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-text-light);
    flex: 0 0 110px;
    text-align: left;
  }

  .td-actions .dropdown {
    margin-left: auto;
  }

  .td-actions::before {
    display: none;
  }

  .td-libelle {
    width: 100%;
    align-items: flex-start;
  }

  .td-libelle::before {
    padding-top: 0.1rem;
  }
}
</style>
