<template>
  <LayoutEmploye>
    <AppCard>
      <h2>Recherche de produits</h2>

      <form @submit.prevent="handleSearch">
        <div class="form-group">
          <label>Code EAN</label>
          <input
            v-model="searchEAN"
            type="text"
            placeholder="3700523456789"
            maxlength="13"
            autofocus
          />
        </div>

        <div class="form-group">
          <label>Libellé du produit</label>
          <input
            v-model="searchLibelle"
            type="text"
            placeholder="Ex: Flip 7, Lego..."
          />
        </div>

        <div class="form-group">
          <label>Fournisseur</label>
          <input
            v-model="searchFournisseur"
            type="text"
            placeholder="Ex: Mattel, Blackrock..."
          />
        </div>

        <button class="primary-btn" type="submit">Rechercher</button>
      </form>

      <!-- Message de chargement -->
      <MessageBox
        v-if="loading"
        type="loading"
        message="Recherche en cours..."
      />

      <!-- Meesage d'erreur -->
      <MessageBox v-if="error" type="error" :message="error" />

      <!-- Résultat -->
      <section v-if="products.length > 0" class="results" ref="resultsSection">
        <h3>Résultats ({{ products.length }})</h3>

        <div class="product-list">
          <article
            v-for="product in products"
            :key="product.id"
            class="product-item"
            @click="goToProduct(product.id)"
          >
            <div class="product-info">
              <h4>{{ product.libelle }}</h4>
              <p>EAN : {{ product.ean }}</p>
              <p>Fournisseur : {{ product.fournisseur || "Non renseigné" }}</p>
              <p class="stock">Stock : {{ product.quantite }}</p>
            </div>
          </article>
        </div>

        <AppPagination
          :current-page="currentPage"
          :total-pages="totalPages"
          @change="fetchPage"
        />
      </section>

      <!-- Aucun résulat -->
      <MessageBox
        v-if="!loading && searched && products.length === 0"
        type="info"
        message="Aucun produit trouvé"
      />
    </AppCard>
  </LayoutEmploye>
</template>

<script setup>
import { ref, nextTick } from "vue";
import { useRouter } from "vue-router";
import { searchProducts } from "@/services/productApi";
import MessageBox from "@/components/MessageBox.vue";
import AppCard from "@/components/AppCard.vue";
import LayoutEmploye from "@/components/employe/LayoutEmploye.vue";
import AppPagination from "@/components/AppPagination.vue";

const router = useRouter();

// Champs du formulaire
const searchEAN = ref("");
const searchLibelle = ref("");
const searchFournisseur = ref("");

// État de la recherche
const products = ref([]);
const loading = ref(false);
const error = ref(null);
const searched = ref(false);
const resultsSection = ref(null);

// Pagination — les 3 nouvelles refs
const currentPage = ref(1);
const totalPages = ref(0);
const activeFilters = ref({});

// Appelée au clic sur "Rechercher"
async function handleSearch() {
  error.value = null;
  products.value = [];
  searched.value = false;
  currentPage.value = 1;

  if (!searchEAN.value && !searchLibelle.value && !searchFournisseur.value) {
    error.value = "Veuillez remplir au moins un champ de recherche";
    return;
  }

  // On sauvegarde les filtres pour pouvoir les réutiliser quand on change de page
  activeFilters.value = {
    ean: searchEAN.value,
    libelle: searchLibelle.value,
    fournisseur: searchFournisseur.value,
  };

  await fetchPage(1);
}

// Appelée à chaque changement de page
async function fetchPage(page) {
  loading.value = true;
  try {
    const data = await searchProducts(activeFilters.value, page);

    if (data.count === 1 && page === 1) {
      router.push(`/product/${data.data[0].id}`);
      return;
    }

    products.value = data.data;
    currentPage.value = data.page;
    totalPages.value = Math.ceil(data.total / data.limit);
    searched.value = true;

    await nextTick();
    resultsSection.value?.scrollIntoView({
      behavior: "smooth",
      block: "start",
    });
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

function goToProduct(productId) {
  router.push(`/product/${productId}`);
}
</script>

<style scoped>
h2 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  color: var(--color-text-dark);
}

/* Style des champs formulaire */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: var(--color-text-dark);
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-input);
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* --- BOUTON --- */

.primary-btn {
  padding: 0.8rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: background 0.2s;
}

.primary-btn:hover {
  background: var(--color-primary-dark);
}

/* Résultats */
.results {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid var(--color-border);
  scroll-margin-top: 8rem;
}

.results h3 {
  color: var(--color-text-dark);
  margin-bottom: 1.5rem;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  background: var(--color-bg-soft);
  padding: 1.5rem;
  border-radius: var(--radius-input);
  border: 2px solid var(--color-border);
  transition:
    border-color 0.2s,
    box-shadow 0.2s,
    transform 0.2s;
  cursor: pointer;
}

.product-item:hover {
  border-color: var(--color-primary);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
  transform: translateY(-2px);
}

.product-info h4 {
  color: var(--color-text-dark);
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.product-info p {
  color: var(--color-text-light);
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.product-info .stock {
  color: var(--color-primary);
  font-weight: 600;
  margin-top: 0.5rem;
}

</style>
