<template>
  <form class="form-card" @submit.prevent="handleSubmit">
    <MessageBox v-if="loading" type="loading" message="Ajustement en cours..." />
    <MessageBox v-if="error" type="error" :message="error" />

    <p class="adjust-intro">
      {{ sens === "ajout" ? "Ajouter au" : "Retirer du" }} stock
      <strong>{{ depotLabel(user?.magasin) }}</strong>
      de « {{ product.libelle }} »
    </p>

    <div class="form-group">
      <label for="adjust-quantite">Quantité</label>
      <input
        id="adjust-quantite"
        v-model.number="quantite"
        type="number"
        min="1"
        class="form-input"
      />
    </div>

    <!-- Aperçu du mouvement, recalculé en direct -->
    <p class="adjust-preview">
      Stock actuel : <strong>{{ stockActuel }}</strong> →
      Après : <strong>{{ stockApres }}</strong>
    </p>

    <!-- Avertissement plancher à 0 -->
    <MessageBox
      v-if="tombeAZero"
      type="info"
      message="Le retrait dépasse le stock disponible : il sera ramené à 0."
    />

    <div class="form-actions">
      <button type="button" class="btn-annuler" @click="emit('cancel')">
        Annuler
      </button>
      <button type="submit" class="btn-valider" :disabled="loading">
        Confirmer
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, computed } from "vue";
import { useAuth } from "@/composables/useAuth";
import { adjustStock } from "@/services/productApi";
import MessageBox from "@/components/MessageBox.vue";

import "@/assets/form.css";

const props = defineProps({
  product: { type: Object, required: true },
  sens: { type: String, required: true }, // 'ajout' | 'retrait'
});

const emit = defineEmits(["success", "cancel"]);
const { user } = useAuth();

const quantite = ref(1);
const loading = ref(false);
const error = ref(null);

function depotLabel(depot) {
  return depot === "tours_nord" ? "Tours Nord" : "Tours Centre";
}

// Stock actuel du dépôt de l'admin connecté
const stockActuel = computed(() =>
  user.value?.magasin === "tours_nord"
    ? (props.product.qte_nord ?? 0)
    : (props.product.qte_centre ?? 0),
);

// Variation SIGNÉE selon le sens : c'est ici que le bouton devient un signe
const delta = computed(() =>
  props.sens === "retrait" ? -quantite.value : quantite.value,
);

// Stock après opération, plafonné à 0 (miroir du backend, pour l'aperçu)
const stockApres = computed(() => Math.max(0, stockActuel.value + delta.value));

// Le retrait dépasse-t-il le stock disponible ?
const tombeAZero = computed(
  () => props.sens === "retrait" && quantite.value > stockActuel.value,
);

async function handleSubmit() {
  error.value = null;

  if (!Number.isInteger(quantite.value) || quantite.value < 1) {
    error.value = "La quantité doit être un entier positif";
    return;
  }

  loading.value = true;
  try {
    const updated = await adjustStock(props.product.id, delta.value);
    emit("success", updated); // le produit à jour renvoyé par l'API
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.adjust-intro {
  margin-bottom: 1rem;
  color: var(--color-text-dark);
}
.adjust-preview {
  margin: 0.5rem 0 1rem;
  color: var(--color-text-light);
}
</style>
