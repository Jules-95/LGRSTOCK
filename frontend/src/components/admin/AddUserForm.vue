<template>
  <form class="user-form" @submit.prevent="handleSubmit">

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
import { addUser } from '@/services/userApi'
import MessageBox from '@/components/MessageBox.vue'

import '@/assets/form.css'

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
.user-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

</style>