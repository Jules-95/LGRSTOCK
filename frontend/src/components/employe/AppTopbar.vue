<template>
  <header class="topbar">
    <span class="topbar-logo">LGR Stock</span>
    <div class="topbar-right">
      <span class="topbar-user"
        >{{ user?.username }} · {{ user?.magasin }}</span
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
  padding: 0.875rem 1.5rem;
  position: sticky;
  top: 0;
  z-index: 10;
}

.topbar-logo {
  font-size: 0.95rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.04em;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.topbar-user {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.55);
}

.topbar-btn {
  background: none;
  border: none;
  color: var(--color-danger);
  font-size: 0.85rem;
  cursor: pointer;
  padding: 0;
}
</style>
