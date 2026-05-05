<template>
  <form class="form-card" @submit.prevent="handleSubmit">

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
import { editProduct } from '@/services/productApi'
import MessageBox from '@/components/MessageBox.vue'

import '@/assets/form.css'

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
