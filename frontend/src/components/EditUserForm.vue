<template>
  <form class="product-form" @submit.prevent="handleSubmit">

    <MessageBox v-if="loading" type="loading" message="Modification en cours..." />
    <MessageBox v-if="error" type="error" :message="error" />

    <div class="form-group">
      <label for="edit-username">Nom d'utilisateur *</label>
      <input
        id="edit-username"
        v-model="form.username"
        type="text"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-password">Nouveau mot de passe</label>
      <input
        id="edit-password"
        v-model="form.password"
        type="password"
        placeholder="Laisser vide pour ne pas modifier"
        class="form-input"
      />
    </div>

    <div class="form-group">
      <label for="edit-role">Rôle *</label>
      <select id="edit-role" v-model="form.role" class="form-input">
        <option value="employe">Employé</option>
        <option value="admin">Admin</option>
      </select>
    </div>

    <div class="form-group">
      <label for="edit-magasin">Magasin *</label>
      <select id="edit-magasin" v-model="form.magasin" class="form-input">
        <option value="tours_nord">Tours Nord</option>
        <option value="tours_centre">Tours Centre</option>
      </select>
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
import { editUser } from '@/services/api'
import MessageBox from '@/components/MessageBox.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['success', 'cancel'])

const form = ref({
  username: props.user.username,
  password: '',
  role:     props.user.role,
  magasin:  props.user.magasin,
})

const loading = ref(false)
const error   = ref(null)

async function handleSubmit() {
  error.value = null

  if (!form.value.username.trim()) {
    error.value = "Le nom d'utilisateur est obligatoire"
    return
  }

  if (form.value.password && form.value.password.length < 5) {
    error.value = 'Le mot de passe doit contenir au moins 5 caractères'
    return
  }

  loading.value = true

  try {
    await editUser(props.user.id, form.value)
    emit('success', { 
      id:      props.user.id, 
      username: form.value.username,
      role:     form.value.role,
      magasin:  form.value.magasin,
    })
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