<!-- frontend/src/components/admin/ExportSection.vue -->
<template>
  <div>
    <div class="content-header">
      <h2 class="content-title">Export CSV</h2>
    </div>

    <p class="section-description">
      Télécharge l'intégralité du stock au format CSV, compatible Excel.
    </p>

    <div class="export-card">
      <div class="export-info">
        <span class="export-icon">📄</span>
        <div>
          <p class="export-label">Stock complet — tous les produits</p>
          <p class="export-hint">Fichier .csv</p>
        </div>
      </div>

      <button
        class="btn-valider"
        :disabled="loading"
        @click="handleExport"
      >
        {{ loading ? 'Export en cours...' : 'Télécharger' }}
      </button>
    </div>

    <p v-if="errorMessage" class="state-message state-message--error">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { exportProducts } from '@/services/productApi'

const loading = ref(false)
const errorMessage = ref('')

async function handleExport() {
  loading.value = true
  errorMessage.value = ''

  try {
    await exportProducts()
  } catch (error) {
    errorMessage.value = error.message
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.section-description {
  color: var(--color-text-light);
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.export-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-card);
  padding: 1.25rem 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.export-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.export-icon {
  font-size: 2rem;
}

.export-label {
  font-weight: 600;
  color: var(--color-text-dark);
  font-size: 0.95rem;
}

.export-hint {
  font-size: 0.8rem;
  color: var(--color-text-light);
  margin-top: 0.2rem;
}

.btn-valider {
  flex: 0;
}
</style>
