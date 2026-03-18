<template>
  <main class="page">
    <div class="container">
      <nav class="back-button">
        <button @click="$router.push('/')" class="btn-back">← Retour</button>
      </nav>

      <article class="card-item">
        <header class="form-header">
          <h1>Ajouter un produit</h1>
        </header>

        <!-- MessageBox réutilisé pour les états loading/erreur/succès -->
        <MessageBox v-if="loading" type="loading" message="Ajout en cours..." />
        <MessageBox v-if="error" type="error" :message="error" />

        <form class="product-form" @submit.prevent="handleSubmit">
          <div class="form-group">
            <label for="libelle">Libellé *</label>
            <input
              id="libelle"
              v-model="form.libelle"
              type="text"
              placeholder="Nom du produit"
              class="form-input"
            />
          </div>

          <div class="form-group">
            <label for="ean">Code EAN *</label>
            <input
              id="ean"
              v-model="form.ean"
              type="text"
              placeholder="13 chiffres"
              maxlength="13"
              class="form-input"
            />
          </div>

          <div class="form-group">
            <label for="fournisseur">Fournisseur</label>
            <input
              id="fournisseur"
              v-model="form.fournisseur"
              type="text"
              placeholder="Nom du fournisseur (optionnel)"
              class="form-input"
            />
          </div>

          <div class="form-group">
            <label for="quantite">Quantité initiale</label>
            <input
              id="quantite"
              v-model="form.quantite"
              type="number"
              min="0"
              class="form-input"
            />
          </div>

          <div class="form-actions">
            <button type="button" class="btn-annuler" @click="$router.back()">
              Annuler
            </button>
            <button type="submit" class="btn-valider" :disabled="loading">
              Ajouter le produit
            </button>
          </div>
        </form>
      </article>
    </div>
  </main>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { addProduct } from "@/services/api";
import MessageBox from "@/components/MessageBox.vue";

const router = useRouter();

// Les données du formulaire — un objet avec un champ par input
const form = ref({
  libelle: "",
  ean: "",
  fournisseur: "",
  quantite: 0,
});

// Les états de la page
const loading = ref(false);
const error = ref(null);

async function handleSubmit() {
  error.value = null;

  // Validation coté client
  // Vérification avant d'appeler l'API
  // Eviter les requêtes inutiles au serveur. 
  if (!form.value.libelle.trim()) {
    error.value = 'Le libellé est obligatoire'
    return
  }

  if (!form.value.ean.trim()) {
    error.value = 'Le code EAN est obligatoire'
    return
  }

  loading.value = true


  try {
    // On envoie tout l'objet form.value à api.js
    const result = await addProduct(form.value);

    // PHP nous renvoie l'ID du nouveau produit
    // On redirige directement vers sa fiche
    router.push(`/product/${result.id}`);
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}
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

.card-item {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f3f4f6;
}

.form-header h1 {
  font-size: 1.75rem;
  color: #1f2937;
  margin: 0;
}

.product-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #1f2937;
}

.form-input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #667eea;
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

.btn-valider:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-valider:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
