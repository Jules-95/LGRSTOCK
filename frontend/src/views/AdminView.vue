<template>
  <div class="admin-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="sidebar-logo">LGR STOCK</span>
        <span class="sidebar-role">Dashboard admin</span>
      </div>

      <nav class="sidebar-nav">
        <button class="nav-item nav-item--disabled" disabled>
          Vue d'ensemble
        </button>

        <button
          class="nav-item"
          :class="{ active: currentSection === 'produits' }"
          @click="currentSection = 'produits'"
        >
          Produits
        </button>

        <button
          class="nav-item"
          :class="{ active: currentSection === 'ajouter' }"
          @click="currentSection = 'ajouter'"
        >
          Ajouter un produit
        </button>

        <button class="nav-item nav-item--disabled" disabled>
          Listes de transfert
        </button>

        <button class="nav-item nav-item--disabled" disabled>
          Gestion utilisateurs
        </button>

        <button class="nav-item nav-item--disabled" disabled>Import CSV</button>

        <button class="nav-item nav-item--disabled" disabled>Export CSV</button>
      </nav>

      <div class="sidebar-footer">
        <span class="sidebar-user"
          >{{ user?.username }} · {{ user?.magasin }}</span
        >
        <button class="btn-logout" @click="handleLogout">Déconnexion</button>
      </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="admin-content">
      <div v-if="currentSection === 'produits'">

        <!-- En-tête -->
        <div class="content-header">
          <h1 class="content-title">Produits</h1>
        </div>

        <MessageBox 
        v-if="successMessage"
        type="info"
        :message="successMessage"
        />

        <!-- Formulaire de recherche -->
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

        <!-- États -->
        <div v-if="loading" class="state-message">Chargement...</div>
        <div
          v-else-if="errorMessage"
          class="state-message state-message--error"
        >
          {{ errorMessage }}
        </div>
        <div
          v-else-if="searched && products.length === 0"
          class="state-message"
        >
          Aucun produit trouvé.
        </div>

        <!-- Tableau produits -->
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

        <!-- Message par défaut avant toute recherche -->
        <div v-else class="state-message">
          Utilisez les barres de recherche pour trouver des produits.
        </div>
      </div>

      <!-- Vue de AjouterProduit -->
      <div v-if="currentSection === 'ajouter'">
        <AddProductForm @cancel="currentSection = 'produits'" />
      </div>
    </main>

    <Modal 
    v-if="showEditModal"
    title="Modifier le produit"
    @close="showEditModal = false"
    >
      <EditProductForm
      :product="selectedProduct"
      @success="handleEditSucess"
      @cancel="showEditModal = false"
      />
  
  </Modal>
  </div>
</template>

<!-- SCRIPTS -->

<script setup>
import { ref } from "vue";
import { useAuth } from "@/composables/useAuth";
import { searchProducts, deleteProduct } from "@/services/api";
import AddProductForm from "@/components/AddProductForm.vue";
import EditProductForm from "@/components/EditProductForm.vue";
import Modal from "@/components/Modal.vue";
import MessageBox from "@/components/MessageBox.vue";

const { user, logout } = useAuth();

const currentSection = ref("produits");
const filters = ref({
  ean: "",
  libelle: "",
  fournisseur: "",
});
const products = ref([]);
const loading = ref(false);
const errorMessage = ref("");
const searched = ref(false);
const successMessage = ref("")

// Etat de la modale d'édition 
const showEditModal = ref(false)
const selectedProduct = ref(null)

async function handleLogout() {
  await logout();
}

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

function openEditModal (product) {
  selectedProduct.value = product
  showEditModal.value = true
}

function handleEditSucess(updatedProduct) {
  const index = products.value.findIndex(p => p.id === updatedProduct.id)
  if (index !== -1) {
    products.value[index] = updatedProduct
  }

  showEditModal.value = false
  successMessage.value = 'Produit modifié avec succès'

  setTimeout(() => { successMessage.value = ''}, 3000)
}


async function confirmDelete(product) {
  if (!confirm(`Supprimer "${product.libelle}" ?`)) return;

  try {
    await deleteProduct(product.id);
    products.value = products.value.filter((p) => p.id !== product.id);
    successMessage.value = 'Produit supprimé avec succès'
    setTimeout(() => { successMessage.value = ''}, 3000)
  } catch (err) {
    errorMessage.value = err.message;
  }
}
</script>

<!-- STYLES -->

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}

/* ── SIDEBAR ── */
.sidebar {
  width: 220px;
  flex-shrink: 0;
  background: #2d1f5e;
  display: flex;
  flex-direction: column;
  padding: 1.5rem 0;
}

.sidebar-header {
  padding: 0 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 1rem;
}

.sidebar-logo {
  display: block;
  font-size: 1rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.04em;
}

.sidebar-role {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 0.2rem;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.nav-item {
  padding: 0.75rem 1.25rem;
  text-align: left;
  background: none;
  border: none;
  border-left: 3px solid transparent;
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.15s;
}

.nav-item.active {
  color: white;
  border-left-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.06);
}

.nav-item:hover:not(.active):not(:disabled) {
  color: rgba(255, 255, 255, 0.8);
}

.nav-item--disabled {
  color: rgba(255, 255, 255, 0.25) !important;
  cursor: not-allowed;
  font-style: italic;
}

.sidebar-footer {
  padding: 1rem 1.25rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-user {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.35);
  margin-bottom: 0.75rem;
}

.btn-logout {
  background: none;
  border: none;
  color: #f87171;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
}

/* ── CONTENU ── */
.admin-content {
  flex: 1;
  background: var(--color-bg-light);
  padding: 2rem;
  overflow-y: auto;
}

.content-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.content-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-dark);
}

.btn-add {
  padding: 0.6rem 1.25rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-add:hover {
  background: var(--color-primary-dark);
}

/* ── RECHERCHE ── */
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

/* ── TABLEAU ── */
.product-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: var(--radius-card);
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.product-table th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-light);
  border-bottom: 1.5px solid var(--color-border);
}

.product-table td {
  padding: 0.85rem 1rem;
  font-size: 0.9rem;
  color: var(--color-text-dark);
  border-bottom: 1px solid var(--color-border);
}

.product-table tr:last-child td {
  border-bottom: none;
}

.td-ean {
  font-family: monospace;
  font-size: 0.8rem;
  color: var(--color-text-light);
}

.td-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit {
  padding: 0.3rem 0.75rem;
  border: 1.5px solid var(--color-border);
  background: var(--color-bg-soft);
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  color: var(--color-text-medium);
}

.btn-delete {
  padding: 0.3rem 0.75rem;
  border: 1.5px solid #fecaca;
  background: #fff5f5;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  color: #dc2626;
}

/* ── ÉTATS ── */
.state-message {
  padding: 2rem;
  text-align: center;
  color: var(--color-text-light);
  background: white;
  border-radius: var(--radius-card);
}

.state-message--error {
  color: #991b1b;
  background: #fee2e2;
}

.btn-back {
  padding: 0.6rem 1.25rem;
  border: 1.5px solid var(--color-border);
  background: white;
  border-radius: var(--radius-btn);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  color: var(--color-text-medium);
}

.btn-back:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}
</style>
