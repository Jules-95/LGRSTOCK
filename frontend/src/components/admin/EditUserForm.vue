<template>
  <form class="form-card" @submit.prevent="handleSubmit">

    <MessageBox v-if="loading" type="loading" message="Modification en cours..." />
    <MessageBox v-if="error" type="error" :message="error" />

    <div class="form-group">
      <label for="edit-username">Nom d'utilisateur *</label>
      <input
        id="edit-username"
        v-model="form.username"
        type="text"
        autocomplete="username"
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
        autocomplete="new-password"
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
import { editUser } from '@/services/userApi'
import MessageBox from '@/components/MessageBox.vue'

import '@/assets/form.css'

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

  if (form.value.password && form.value.password.length < 8) {
    error.value = 'Le mot de passe doit contenir au moins 8 caractères'
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
