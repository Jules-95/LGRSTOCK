<template>
  <div>
    <div class="content-header">
      <h1 class="content-title">Gestion utilisateurs</h1>
      <button class="btn-add" @click="showAddUserModal = true">
        + Ajouter un utilisateur
      </button>
    </div>

    <MessageBox v-if="successMessage" type="info" :message="successMessage" />
    <MessageBox v-if="errorMessage" type="error" :message="errorMessage" />

    <div v-if="loading" class="state-message">Chargement...</div>

    <table v-else-if="users.length > 0" class="product-table">
      <thead>
        <tr>
          <th>Nom d'utilisateur</th>
          <th>Rôle</th>
          <th>Magasin</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="u in users" :key="u.id">
          <td>{{ u.username }}</td>
          <td>{{ u.role }}</td>
          <td>{{ u.magasin }}</td>
          <td class="td-actions">
            <button class="btn-edit" @click="openEditModal(u)">Modifier</button>
            <button class="btn-delete" @click="confirmDelete(u)">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-else class="state-message">Aucun utilisateur trouvé.</div>

    <Modal v-if="showAddUserModal" title="Ajouter un utilisateur" @close="showAddUserModal = false">
      <AddUserForm
        @success="handleAddSuccess"
        @cancel="showAddUserModal = false"
      />
    </Modal>

    <Modal v-if="showEditModal" title="Modifier l'utilisateur" @close="showEditModal = false">
      <EditUserForm
        :user="selectedUser"
        @success="handleEditSuccess"
        @cancel="showEditModal = false"
      />
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { getUsers, deleteUser } from '@/services/userApi'
import AddUserForm from '@/components/admin/AddUserForm.vue'
import EditUserForm from '@/components/admin/EditUserForm.vue'
import Modal from '@/components/Modal.vue'
import MessageBox from '@/components/MessageBox.vue'

import '@/assets/admin.css'

const users = ref([])
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const showAddUserModal = ref(false)
const showEditModal = ref(false)
const selectedUser = ref(null)

async function loadUsers() {
  loading.value = true
  errorMessage.value = ''

  try {
    const result = await getUsers()
    users.value = result.data ?? []
  } catch (err) {
    errorMessage.value = err.message
  } finally {
    loading.value = false
  }
}

function openEditModal(u) {
  selectedUser.value = u
  showEditModal.value = true
}

function handleAddSuccess() {
  showAddUserModal.value = false
  successMessage.value = 'Utilisateur ajouté avec succès'
  setTimeout(() => { successMessage.value = '' }, 3000)
  loadUsers()
}

function handleEditSuccess(updatedUser) {
  const index = users.value.findIndex(u => u.id === updatedUser.id)
  if (index !== -1) {
    users.value[index] = updatedUser
  }
  showEditModal.value = false
  successMessage.value = 'Utilisateur modifié avec succès'
  setTimeout(() => { successMessage.value = '' }, 3000)
}

async function confirmDelete(u) {
  if (!confirm(`Supprimer "${u.username}" ?`)) return

  try {
    await deleteUser(u.id)
    users.value = users.value.filter(u2 => u2.id !== u.id)
    successMessage.value = 'Utilisateur supprimé avec succès'
    setTimeout(() => { successMessage.value = '' }, 3000)
  } catch (err) {
    errorMessage.value = err.message
    setTimeout(() => { errorMessage.value = '' }, 3000)
  }
}

defineExpose({ loadUsers })
</script>

<style scoped>

.btn-add {
  padding: 0.6rem 1.25rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add:hover {
  background: var(--color-primary-dark);
}

</style>