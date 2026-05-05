<template>
  <header class="topbar">
    <span class="topbar-logo">LGR Stock</span>
    <div v-if="user" class="topbar-right">
      <span class="topbar-user"
        >{{ user.username }} · {{ user.magasin }}</span
      >
      <button class="topbar-btn" @click="handleLogout">Déconnexion</button>
    </div>
  </header>
</template>

<script setup>
import { useAuth } from "@/composables/useAuth";

const { user, logout } = useAuth();

async function handleLogout() {
  await logout();
}
</script>

<style scoped>
.topbar {
  background: var(--color-nav-bg);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.2rem 1.7rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.topbar-logo {
  font-size: 1.6rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.05em;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 3rem;
}

.topbar-user {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.55);
}

.topbar-btn {
  background: none;
  border: none;
  color: var(--color-danger);
  font-size: 1rem;
  cursor: pointer;
  padding: 0;
}

.topbar-btn:hover {
  opacity: 0.9;
}
</style>
