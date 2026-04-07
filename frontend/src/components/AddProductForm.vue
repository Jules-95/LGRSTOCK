<template>
  <form class="product-form" @submit.prevent="handleSubmit">

    <MessageBox v-if="loading" type="loading" message="Ajout en cours..." />
    <MessageBox v-if="error" type="error" :message="error" />
    <MessageBox v-if="success" type="info" message="Produit ajouté avec succès !"/>

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
      <button type="button" class="btn-annuler" @click="emit('cancel')">
        Annuler
      </button>
      <button type="submit" class="btn-valider" :disabled="loading">
        Ajouter le produit
      </button>
    </div>

  </form>
</template>

<script setup>
import { ref } from 'vue'
import { addProduct } from '@/services/api'
import MessageBox from '@/components/MessageBox.vue'

const emit = defineEmits(['success', 'cancel'])

const form = ref({
  libelle: '',
  ean: '',
  fournisseur: '',
  quantite: 0,
})

const loading = ref(false)
const success = ref(false)
const error   = ref(null)

async function handleSubmit() {
  error.value = null

  if (!form.value.libelle.trim()) {
    error.value = 'Le libellé est obligatoire'
    return
  }

  if (!form.value.ean.trim()) {
    error.value = 'Le code EAN est obligatoire'
    return
  }

  if (!/^\d{13}$/.test(form.value.ean.trim())) {
    error.value = 'Le code EAN doit contenir exactement 13 chiffres'
    return
  }

  loading.value = true

  try {
    const result = await addProduct(form.value)
    success.value = true
    setTimeout(() => { success.value = false}, 3000)
    form.value = { libelle: '', ean: '', fournisseur: '', quantite: 0}
    emit('success', result.id) // 
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.product-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  background: white;
  padding: 2rem;
  border-radius: var(--radius-card);
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
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