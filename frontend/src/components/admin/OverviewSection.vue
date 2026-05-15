<template>
  <div class="page">
    <div class="container">
      <h2>Vue d'ensemble</h2>

      <MessageBox v-if="loading" type="loading" message="Chargement..." />
      <MessageBox v-if="error" type="error" :message="error" />

      <div v-if="stats" class="stats-grid">
        <div class="stat-card">
          <span class="stat-value">{{ stats.total_produits }}</span>
          <span class="stat-label">Produits en stock</span>
        </div>

        <div class="stat-card stat-card--warning">
          <span class="stat-value">{{ stats.produits_rupture }}</span>
          <span class="stat-label">Produits en rupture</span>
        </div>

        <div class="stat-card">
          <span class="stat-value">{{ stats.total_utilisateurs }}</span>
          <span class="stat-label">Utilisateurs</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { getStats } from "@/services/productApi";
import MessageBox from "@/components/MessageBox.vue";

const stats = ref(null);
const loading = ref(false);
const error = ref(null);

onMounted(async () => {
  loading.value = true;
  try {
    stats.value = await getStats();
  } catch (err) {
    error.value = err.message;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.stat-card {
  background: white;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-input);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-card--warning {
  border-color: var(--color-warning, orange);
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-dark);
}

.stat-label {
  font-size: 0.9rem;
  color: var(--color-text-light);
}
</style>