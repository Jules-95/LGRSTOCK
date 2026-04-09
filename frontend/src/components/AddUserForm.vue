<template>
  <form class="product-form" @submit.prevent="handleSubmit">

    <MessageBox v-if="loading" type="loading" message="Ajout en cours..." />
    <MessageBox v-if="error" type="error" :message="error" />
    <MessageBox v-if="success" type="info" message="Utilisateur ajouté avec succès !" />

    <div class="form-group">
      <label for="username">Nom d'utilisateur *</label>
      <input
        id="username"
        v-model="form.username"
        type="text"
        placeholder="Ex: employe_nord"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="password">Mot de passe *</label>
      <input
        id="password"
        v-model="form.password"
        type="password"
        placeholder="Minimum 5 caractères"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="role">Rôle *</label>
      <select id="role" v-model="form.role" class="form-input">
        <option value="">-- Choisir un rôle --</option>
        <option value="employe">Employé</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <div class="form-group">
      <label for="magasin">Magasin *</label>
      <select id="magasin" v-model="form.magasin" class="form-input">
        <option value="">-- Choisir un magasin --</option>
        <option value="tours_nord">Tours Nord</option>
        <option value="tours_centre">Tours Centre</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="button" class="btn-annuler" @click="emit('cancel')">
        Annuler
      </button>
      <button type="submit" class="btn-valider" :disabled="loading">
        Ajouter l'utilisateur
      </button>
    </div>

  </form>
</template>

<script setup>
import { ref } from 'vue'
import { addUser } from '@/services/api'
import MessageBox from '@/components/MessageBox.vue'

const emit = defineEmits(['success', 'cancel'])

const form = ref({
  username: '',
  password: '',
  role: '',
  magasin: '',
})

const loading = ref(false)
const success = ref(false)
const error   = ref(null)

async function handleSubmit() {
  error.value = null

  if (!form.value.username.trim()) {
    error.value = "Le nom d'utilisateur est obligatoire"
    return
  }

  if (form.value.password.length < 5) {
    error.value = 'Le mot de passe doit contenir au moins 5 caractères'
    return
  }

  if (!form.value.role) {
    error.value = 'Le rôle est obligatoire'
    return
  }

  if (!form.value.magasin) {
    error.value = 'Le magasin est obligatoire'
    return
  }

  loading.value = true

  try {
    const result = await addUser(form.value)
    success.value = true
    setTimeout(() => { success.value = false }, 3000)
    form.value = { username: '', password: '', role: '', magasin: '' }
    emit('success', result.id)
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