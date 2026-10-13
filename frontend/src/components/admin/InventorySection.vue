<template>
  <div>
    <div class="content-header">
      <h1 class="content-title">Inventaire - {{ depotLabel(user?.magasin) }}</h1>
    </div>

    <MessageBox v-if="loading" type="loading" message="Chargement..." />
    <MessageBox v-if="error" type="error" :message="error" />

    <!-- A. Aucun inventaire en cours : démarrage avec confirmation forte -->
    <div v-if="!loading && !inventory" class="inventory-card form-card">
      <p class="inventory-warning">
        ⚠️ Démarrer un inventaire remet à <strong>0</strong> le stock de tous
        les produits du dépôt <strong>{{ depotLabel(user?.magasin) }}</strong>.
        L'autre dépôt n'est pas modifié. Pensez à faire un
        <strong>export CSV</strong> avant de commencer.
      </p>

      <div class="form-group">
        <label for="confirmation">
          Pour confirmer, tapez « {{ confirmText }} »
        </label>
        <input
          id="confirmation"
          v-model="confirmation"
          type="text"
          autocomplete="off"
          class="form-input"
        />
      </div>

      <div class="form-actions">
        <button
          type="button"
          class="btn-valider"
          :disabled="confirmation.trim().toUpperCase() !== confirmText || starting"
          @click="handleStart"
        >
          Commencer l'inventaire
        </button>
      </div>
    </div>

    <!-- B. Inventaire en cours -->
    <div v-if="!loading && inventory" class="inventory-card form-card">
      <p>
        Inventaire en cours depuis le
        <strong>{{ formatDate(inventory.started_at) }}</strong>
        (démarré par {{ inventory.started_by ?? "utilisateur supprimé" }})
      </p>

      <!-- Étape 3b : la boucle de scan viendra ici -->

      <div class="form-actions">
        <button type="button" class="btn-annuler" @click="handleFinish">
          Terminer l'inventaire
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useAuth } from "@/composables/useAuth";
import {
  getInventoryStatus,
  startInventory,
  finishInventory,
} from "@/services/inventoryApi";
import MessageBox from "@/components/MessageBox.vue";

import "@/assets/admin.css";
import "@/assets/form.css";

const { user } = useAuth();

const inventory = ref(null); // inventaire en cours ou null
const loading = ref(true);
const starting = ref(false);
const error = ref(null);
const confirmation = ref("");

// Texte à taper pour confirmer : "TOURS CENTRE" ou "TOURS NORD"
const confirmText = computed(() =>
  depotLabel(user.value?.magasin).toUpperCase(),
);

// helper pour afficher "Tours Nord/Centre" au lieu de "tours_nord/_centre"
function depotLabel(depot) {
  return depot === "tours_nord" ? "Tours Nord" : "Tours Centre";
}

// "2026-10-10 12:21:00" → "10/10/2026 12:21"
function formatDate(date) {
  return new Date(date.replace(" ", "T")).toLocaleString("fr-FR", {
    dateStyle: "short",
    timeStyle: "short",
  });
}

// Au chargement : y a-t-il déjà un inventaire en cours ?
onMounted(async () => {
  try {
    inventory.value = await getInventoryStatus();
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});

async function handleStart() {
  error.value = null;
  starting.value = true;

  try {
    inventory.value = await startInventory();
    confirmation.value = "";
  } catch (err) {
    error.value = err.message;
  } finally {
    starting.value = false;
  }
}

async function handleFinish() {
  if (!confirm("Terminer l'inventaire ? Les quantités comptées deviennent le stock officiel.")) {
    return;
  }
  error.value = null;

  try {
    await finishInventory();
    inventory.value = null;
  } catch (err) {
    error.value = err.message;
  }
}
</script>

<style scoped>
.inventory-card {
  background: white;
  padding: 2rem;
  border-radius: var(--radius-card);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.inventory-warning {
  padding: 1rem;
  border-radius: var(--radius-btn);
  background: var(--color-danger-bg);
  color: var(--color-danger-text);
  line-height: 1.5;
}
</style>
