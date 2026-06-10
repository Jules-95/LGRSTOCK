<template>
  <div>
    <div class="content-header">
      <h1 class="content-title">Historique Bob</h1>
    </div>

    <form @submit.prevent="handleSearch">
      <div class="search-fields">
        <div class="search-field">
          <label>Code EAN</label>
          <input
            v-model="filters.ean"
            type="text"
            placeholder="3700523456789"
            maxlength="13"
            @focus="$event.target.select()"
          />
        </div>
        <div class="search-field">
          <label>Libellé</label>
          <input
            v-model="filters.libelle"
            type="text"
            placeholder="Ex: Lego, Pokemon..."
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

    <div v-else-if="products.length > 0" class="table-wrapper">
      <table class="product-table">
        <thead>
          <tr>
            <th>Libellé</th>
            <th>EAN</th>
            <th>Fournisseur</th>
            <th>Réf. fourn</th>
            <th>Prix</th>
            <th>Stock</th>
            <th>Activité</th>
            <th>Rayon</th>
            <th>Famille</th>
            <th>Code article</th>
            <th>Millésime</th>
            <th>Code Récréaclub</th>
            <th>Code fournisseur</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id">
            <td>{{ product.libelle }}</td>
            <td>{{ product.ean || "—" }}</td>
            <td>{{ product.fournisseur || "—" }}</td>
            <td>{{ product.ref_fournisseur || "—" }}</td>
            <td>{{ product.prix || "—" }}</td>
            <td>{{ product.stock_local ?? "—" }}</td>
            <td>{{ product.activite || "—" }}</td>
            <td>{{ product.rayon || "—" }}</td>
            <td>{{ product.famille || "—" }}</td>
            <td>{{ product.code_article || "—" }}</td>
            <td>{{ product.millesime || "—" }}</td>
            <td>{{ product.code_recreaclub || "—" }}</td>
            <td>{{ product.code_fournisseur || "—" }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="state-message">
      Utilisez les barres de recherche pour trouver des produits.
    </div>

    <AppPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      @change="fetchPage"
    />

    <div class="bob-import">
      <p class="bob-import-label">Import des données Bob</p>
      <button class="btn-import" disabled>Importer un CSV</button>
      <p class="bob-import-hint">
        Import effectué le 09/06/2026 — Contactez le support pour effectuer un
        nouvel import
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import AppPagination from "@/components/AppPagination.vue";
import "@/assets/admin.css";

const filters = ref({ ean: "", libelle: "", fournisseur: "" });
const products = ref([]);
const loading = ref(false);
const errorMessage = ref("");
const searched = ref(false);
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
    const params = new URLSearchParams();
    if (filters.value.ean) params.append("ean", filters.value.ean);
    if (filters.value.libelle) params.append("libelle", filters.value.libelle);
    if (filters.value.fournisseur)
      params.append("fournisseur", filters.value.fournisseur);
    params.append("page", page);

    const response = await fetch(
      `${import.meta.env.VITE_API_BASE_URL}/bob.php?${params.toString()}`,
      { credentials: "include" },
    );
    const data = await response.json();

    if (data.error) throw new Error(data.message);

    products.value = data.data ?? [];
    currentPage.value = data.page;
    totalPages.value = Math.ceil(data.total / data.limit);
  } catch (err) {
    errorMessage.value = err.message;
  } finally {
    loading.value = false;
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

.table-wrapper {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.product-table tbody tr:nth-child(even) {
  background: var(--color-bg-soft);
}

/* Pour les prix */
.product-table td:nth-child(5) {
  white-space: nowrap;
}

.bob-import {
  margin-top: 3rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.bob-import-label {
  font-size: 0.85rem;
  color: var(--color-text-light);
}

.btn-import {
  padding: 0.5rem 1rem;
  background: var(--color-bg-soft);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-btn);
  font-size: 0.85rem;
  cursor: pointer;
  color: var(--color-text-dark);
}

.btn-import input[type="file"] {
  display: none;
}

.bob-import-hint {
  font-size: 0.8rem;
  color: var(--color-text-light);
  font-style: italic;
}

.btn-import:disabled {
  opacity: 0.4;
  cursor: not-allowed;
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

  .product-table th,
  .product-table td {
    font-size: 0.7rem;
    padding: 0.5rem 0.4rem;
  }
}
</style>
