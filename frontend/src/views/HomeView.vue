<template>
  <PageLayout>
    <header class="header">
      <h1 class="logo">LGR STOCK</h1>
      <p class="subtitle">
        Outil de visualisation et de manipulation de stock de la reserve Colombe
      </p>
      <div class="header-user">
        <span class="header-username">{{ user?.username }}</span>
        <button class="btn-logout" @click="handleLogout">Déconnexion</button>
      </div>
    </header>

    <AppCard>
      <h2>🔍 Recherche de produits</h2>

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

        <button class="primary-btn" type="submit">🔍 Rechercher</button>
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
        <h3>📦 Résultats ({{ products.length }})</h3>

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
      </section>

      <!-- Aucun résulat -->
      <MessageBox
        v-if="!loading && searched && products.length === 0"
        type="info"
        message="Aucun produit trouvé"
      />
    </AppCard>
  </PageLayout>
</template>

<script setup>
import { ref, nextTick } from "vue";
import { useRouter } from "vue-router";
import { searchProducts } from "@/services/productApi";
import { useAuth } from '@/composables/useAuth'
import MessageBox from "@/components/MessageBox.vue";
import PageLayout from "@/components/employe/LayoutEmploye.vue";
import AppCard from "@/components/AppCard.vue";


// Hook Vue Router qui donne accès à l'objet de navigation
const router = useRouter();

// Variables réactives pour stocker ce que l'utilisateur entre dans un champ
const searchEAN = ref("");
const searchLibelle = ref("");
const searchFournisseur = ref("");

// Nouvelle variables pour gérer l'état de la recherche (connexion API)
const products = ref([]); // Liste des produits trouvés
const loading = ref(false); // Indique si une recherche est en cours
const error = ref(null); // Message d'erreur éventuel
const searched = ref(false); // Indique si un erecherche a été lancée
const resultsSection = ref(null); // Ref qui pointe vers l'élément DOM

// Récupération de logout et user
const { user, logout } = useAuth()

async function handleLogout() {
  await logout()
}

// Fonction appelé au clique sur le btn "Rechercher"
async function handleSearch() {
  // Réinitialiser l'état
  error.value = null;
  products.value = [];
  searched.value = true;

  // Vérifier qu'au moins un champ est rempli
  if (!searchEAN.value && !searchLibelle.value && !searchFournisseur.value) {
    error.value = "Veuillez remplir au moins un champ de recherche";
    return;
  }

  loading.value = true;

  try {
    const data = await searchProducts({
      ean: searchEAN.value,
      libelle: searchLibelle.value,
      fournisseur: searchFournisseur.value,
    });

    //Redirection automatique : Cas où un seul résultat
    if (data.count === 1) {
      router.push(`/product/${data.data[0].id}`);
    } else {
      // Sinon affichage de la liste de résultat
      products.value = data.data;
      await nextTick();
      resultsSection.value?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  } catch (err) {
    // Le service a lancé une erreur (throw new Error)
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}

/**
 * Navigation vers la page de détails d'un produit
 * @param {numbner} productId - L''ID du produit à afficher'
 */

function goToProduct(productId) {
  router.push(`/product/${productId}`);
  // Si productId = 5 -> résultat : "/product/5"
}
</script>

<style scoped>

/* Affichage de la fonctionnalité de deconnexion */
.header-user {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  justify-content: center;
  margin-top: 1rem;
}

.header-username {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.btn-logout {
  background: none;
  border: 1.5px solid, rgba(255, 255, 255, 0.4);
  color: white;
  padding: 0.4rem 1rem;
  border-radius: var(--radius-btn);
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-logout:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Header commun à toutes les vues */
.header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  font-size: 3rem;
  font-weight: 800;
  color: white;
  margin-bottom: 0.5rem;
}

.subtitle {
  color: white;
  font-size: 1.1rem;
}

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
  border: 2px solid var(--color-border);
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
  padding: 1rem;
  background: linear-gradient(
    135deg,
    var(--color-primary) 0%,
    var(--color-secondary) 100%
  );
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
}

/* Résultats */
.results {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid var(--color-bg-light);
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
  transition: all 0.2s;
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
