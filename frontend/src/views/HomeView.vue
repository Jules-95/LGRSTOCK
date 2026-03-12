<template>
  <main class="home">
    <div class="container">
      <header class="header">
        <h1 class="logo">LGR STOCK</h1>
        <p class="subtitle">
          Outil de visualisation et de manipulation de stock de la reserve
          Colombe
        </p>
      </header>

      <section class="card-item">
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

          <button class="search-btn" type="submit">🔍 Rechercher</button>
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
        <section v-if="products.length > 0" class="results">
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
                <p>Fournisseur : {{ product.fournisseur || "Non reseigné" }}</p>
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
      </section>
    </div>
  </main>
</template>

<script setup>
import { API_BASE_URL } from "@/config";
import { ref } from "vue";
import { useRouter } from "vue-router";

import MessageBox from "@/components/MessageBox.vue";

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

  // Construire l'URL de l'API avec les paramètres
  const params = new URLSearchParams();
  if (searchEAN.value) params.append("ean", searchEAN.value);
  if (searchLibelle.value) params.append("libelle", searchLibelle.value);
  if (searchFournisseur.value)
    params.append("fournisseur", searchFournisseur.value);

  const apiURL = `${API_BASE_URL}/search.php?${params.toString()}`;

  console.log("URL appelée :", apiURL);

  loading.value = true;

  try {
    const response = await fetch(apiURL);
    const data = await response.json();

    console.log("Réponse API :", data);

    if (data.error) {
      error.value = data.message;
    } else {
      // Si un seul résultat : Redirection automatique vers fiche produit
      if (data.count === 1) {
        console.log("Redirection auto vers produit", data.data[0].id);
        router.push(`/product/${data.data[0].id}`);
      } else {
        // Sinon affichage de la liste
        products.value = data.data;
      }
    }
  } catch (err) {
    console.error("Erreur :", err);
    error.value =
      "Erreur lors de la recherche. Vérifiez que le backend est actif.";
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
/* Style qui sera commun (UTILISATION DE COMPOSANT ?) */

/* Mis en page de toutes les vues  */
.home {
  min-height: 100vh;
  padding: 2rem;
}

.container {
  max-width: 800px;
  margin: 0 auto;
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

/* Style des cards conteneurs */
.card-item {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.card-item h2 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

/* Style des champs formulaire */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* --- BOUTON --- */

.search-btn {
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all ease 1s;
}

/* Résultats */
.results {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
}

.results h3 {
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  transition: all 0.2s;
  cursor: pointer;
}

.product-item:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
  transform: translateY(-2px);
}

.product-info h4 {
  color: #1f2937;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.product-info p {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.product-info .stock {
  color: #667eea;
  font-weight: 600;
  margin: top 0.5rem;
}
</style>
