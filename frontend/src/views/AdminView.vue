<template>
  <div class="admin-layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <span class="sidebar-logo">LGR STOCK</span>
        <span class="sidebar-role">Dashboard admin</span>
      </div>

      <nav class="sidebar-nav">
        <button class="nav-item nav-item--disabled" disabled>
          Vue d'ensemble
        </button>
        <button
          class="nav-item"
          :class="{ active: currentSection === 'produits' }"
          @click="currentSection = 'produits'"
        >
          Gestion produits
        </button>
        <button
          class="nav-item"
          :class="{ active: currentSection === 'ajouter' }"
          @click="currentSection = 'ajouter'"
        >
          Ajouter un produit
        </button>
        <button
          class="nav-item"
          :class="{ active: currentSection === 'utilisateurs' }"
          @click="currentSection = 'utilisateurs'"
        >
          Gestion utilisateurs
        </button>
        <button class="nav-item nav-item--disabled" disabled>Listes de transfert</button>
        <button class="nav-item nav-item--disabled" disabled>Import CSV</button>
        <button class="nav-item nav-item--disabled" disabled>Export CSV</button>
      </nav>

      <div class="sidebar-footer">
        <span class="sidebar-user">{{ user?.username }} · {{ user?.magasin }}</span>
        <button class="btn-logout" @click="handleLogout">Déconnexion</button>
      </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="admin-content">
      <ProductSection v-if="currentSection === 'produits'" />

      <div v-if="currentSection === 'ajouter'">
        <div class="content-header">
          <h1 class="content-title">Ajouter un produit</h1>
        </div>
        <AddProductForm @cancel="currentSection = 'produits'" />
      </div>

      <UserSection v-if="currentSection === 'utilisateurs'" ref="userSectionRef" />
    </main>
  </div>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import ProductSection from '@/components/admin/ProductSection.vue'
import UserSection from '@/components/admin/UserSection.vue'
import AddProductForm from '@/components/admin/AddProductForm.vue'

const { user, logout } = useAuth()

const currentSection = ref('produits')
const userSectionRef = ref(null)

watch(currentSection, async (newSection) => {
  if (newSection === 'utilisateurs') {
    await nextTick()
    console.log('userSectionRef:', userSectionRef.value)
    userSectionRef.value?.loadUsers()
  }
})

async function handleLogout() {
  await logout()
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 220px;
  flex-shrink: 0;
  background: #2d1f5e;
  display: flex;
  flex-direction: column;
  padding: 1.5rem 0;
}

.sidebar-header {
  padding: 0 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 1rem;
}

.sidebar-logo {
  display: block;
  font-size: 1rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.04em;
}

.sidebar-role {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 0.2rem;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.nav-item {
  padding: 0.75rem 1.25rem;
  text-align: left;
  background: none;
  border: none;
  border-left: 3px solid transparent;
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.15s;
}

.nav-item.active {
  color: white;
  border-left-color: var(--color-primary);
  background: rgba(255, 255, 255, 0.06);
}

.nav-item:hover:not(.active):not(:disabled) {
  color: rgba(255, 255, 255, 0.8);
}

.nav-item--disabled {
  color: rgba(255, 255, 255, 0.25) !important;
  cursor: not-allowed;
  font-style: italic;
}

.sidebar-footer {
  padding: 1rem 1.25rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-user {
  display: block;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.35);
  margin-bottom: 0.75rem;
}

.btn-logout {
  background: none;
  border: none;
  color: #f87171;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
}

.admin-content {
  flex: 1;
  background: var(--color-bg-light);
  padding: 2rem;
  overflow-y: auto;
}

.content-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.content-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-dark);
}
</style>