<template>
  <PageLayout back-label="← Retour à la recherche">
    <AppCard>
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
          <button type="button" class="btn-annuler" @click="router.back()">
            Annuler
          </button>
          <button type="submit" class="btn-valider" :disabled="loading">
            Ajouter le produit
          </button>
        </div>
      </form>
    </AppCard>
  </PageLayout>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { addProduct } from "@/services/api";
import MessageBox from "@/components/MessageBox.vue";
import PageLayout from "@/components/PageLayout.vue";
import AppCard from "@/components/AppCard.vue";

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
    error.value = "Le libellé est obligatoire";
    return;
  }

  if (!form.value.ean.trim()) {
    error.value = "Le code EAN est obligatoire";
    return;
  }

  loading.value = true;

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

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid var(--color-bg-light);
}

.form-header h1 {
  font-size: 1.75rem;
  color: var(--color-text-dark);
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
  color: var(--color-text-dark);
}

.form-input {
  padding: 0.75rem;
  border: 2px solid var(--color-border);
  border-radius: var(--radius-input);
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: var(--color-primary);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 0.5rem;
}

.btn-annuler {
  flex: 1;
  padding: 0.75rem;
  border: 2px solid var(--color-border);
  border-radius: 10px;
  background: white;
  font-weight: 600;
  cursor: pointer;
  color: var(--color-text-dark);
}

.btn-valider {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 10px;
  background: var(--color-primary);
  color: white;
  font-weight: 600;
  cursor: pointer;
}

.btn-valider:hover:not(:disabled) {
  background: var(--color-primary-dark);
}

.btn-valider:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
