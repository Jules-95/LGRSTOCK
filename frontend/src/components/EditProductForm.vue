<template>
  <form class="product-form" @submit.prevent="handleSubmit">

    <MessageBox v-if="loading" type="loading" message="Modification en cours..." />
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
      <label for="edit-quantite">Quantité</label>
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
import { ref } from 'vue'
import { editProduct } from '@/services/api'
import MessageBox from '@/components/MessageBox.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['success', 'cancel'])

// On initialise le formulaire avec les valeurs du produit reçu en prop
const form = ref({
  libelle:     props.product.libelle,
  ean:         props.product.ean,
  fournisseur: props.product.fournisseur ?? '',
  quantite:    props.product.quantite,
})

const loading = ref(false)
const error   = ref(null)

async function handleSubmit() {
  error.value = null

  if (!form.value.libelle.trim()) {
    error.value = 'Le libellé est obligatoire'
    return
  }

  if (!/^\d{13}$/.test(form.value.ean.trim())) {
    error.value = 'Le code EAN doit contenir exactement 13 chiffres'
    return
  }

  loading.value = true

  try {
    await editProduct(props.product.id, form.value)
    emit('success', { id: props.product.id, ...form.value })
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Identique à AddProductForm — on réutilise les mêmes classes */
.product-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-group label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-light);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.form-input {
  padding: 0.75rem 1rem;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-input);
  font-size: 0.95rem;
  color: var(--color-text-dark);
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
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-btn);
  background: white;
  font-weight: 600;
  cursor: pointer;
  color: var(--color-text-dark);
}

.btn-valider {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: var(--radius-btn);
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