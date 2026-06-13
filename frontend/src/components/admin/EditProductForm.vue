<template>
  <form class="form-card" @submit.prevent="handleSubmit">
    <MessageBox
      v-if="loading"
      type="loading"
      message="Modification en cours..."
    />
    <MessageBox v-if="error" type="error" :message="error" />

    <div class="form-group">
      <label for="edit-libelle">Libellé *</label>
      <input
        id="edit-libelle"
        v-model="form.libelle"
        type="text"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-ean">Code EAN *</label>
      <input
        id="edit-ean"
        v-model="form.ean"
        type="text"
        maxlength="13"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-fournisseur">Fournisseur</label>
      <input
        id="edit-fournisseur"
        v-model="form.fournisseur"
        type="text"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-ref_fournisseur">Référence fournisseur</label>
      <input
        id="edit-ref_fournisseur"
        v-model="form.ref_fournisseur"
        type="text"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-prix">Prix (€)</label>
      <input
        id="edit-prix"
        v-model="form.prix"
        type="number"
        min="0"
        step="0.01"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-quantite"
        >Quantité - {{ depotLabel(user?.magasin) }}</label
      >
      <input
        id="edit-quantite"
        v-model="form.quantite"
        type="number"
        min="0"
        class="form-input"
      />
    </div>

    <div class="form-actions">
      <button type="button" class="btn-annuler" @click="emit('cancel')">
        Annuler
      </button>
      <button type="submit" class="btn-valider" :disabled="loading">
        Enregistrer
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref } from "vue";
import { useAuth } from "@/composables/useAuth";
import { editProduct } from "@/services/productApi";
import MessageBox from "@/components/MessageBox.vue";

import "@/assets/form.css";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["success", "cancel"]);
const { user } = useAuth();

function depotLabel(depot) {
  return depot === "tours_nord" ? "Tours Nord" : "Tours Centre";
}

// La quantité du dépôt de l'admin connecté (pas le total)
const maQuantite =
  user.value?.magasin === "tours_nord"
    ? (props.product.qte_nord ?? 0)
    : (props.product.qte_centre ?? 0);

const form = ref({
  libelle: props.product.libelle,
  ean: props.product.ean,
  fournisseur: props.product.fournisseur ?? "",
  quantite: maQuantite,
  ref_fournisseur: props.product.ref_fournisseur ?? "",
  prix: props.product.prix ?? "",
});

const loading = ref(false);
const error = ref(null);

async function handleSubmit() {
  error.value = null;

  if (!form.value.libelle.trim()) {
    error.value = "Le libellé est obligatoire";
    return;
  }

  if (!/^\d{13}$/.test(form.value.ean.trim())) {
    error.value = "Le code EAN doit contenir exactement 13 chiffres";
    return;
  }

  loading.value = true;

  try {
    await editProduct(props.product.id, form.value);
    emit("success", { id: props.product.id, ...form.value });
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
}
</script>
