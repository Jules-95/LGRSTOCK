<template>
  <div>
    <div class="content-header">
      <h2 class="content-title">Import CSV</h2>
    </div>

    <p class="section-description">
      Importe des produits depuis un fichier CSV. Les produits existants (même EAN) seront mis à jour.
    </p>

    <div class="export-card">
      <div class="export-info">
        <span class="export-icon">📂</span>
        <div>
          <p class="export-label">Fichier CSV uniquement</p>
          <p class="export-hint">Colonnes attendues : ean, libelle, quantite (fournisseur, prix optionnels)</p>
        </div>
      </div>

      <label class="btn-valider btn-label" :class="{ 'btn-disabled': loading }">
        {{ loading ? 'Import en cours...' : 'Selectionner' }}
        <input
          type="file"
          accept=".csv"
          :disabled="loading"
          @change="handleFileChange"
        />
      </label>
    </div>

    <!-- Fichier sélectionné -->
    <div v-if="selectedFile && !loading" class="file-selected">
      <p>📄 {{ selectedFile.name }}</p>
      <button class="btn-valider" @click="handleImport">Importer</button>
    </div>

    <!-- Succès -->
    <p v-if="successMessage" class="state-message state-message--success">
      {{ successMessage }}
    </p>

    <!-- Warnings colonnes ignorées -->
    <div v-if="warnings.length > 0" class="state-message state-message--warning">
      ⚠️ {{ warnings[0] }}
    </div>

    <!-- Erreurs de validation -->
    <div v-if="validationErrors.length > 0" class="validation-errors">
      <p class="error-title">{{ errorMessage }}</p>
      <ul>
        <li v-for="(err, index) in validationErrors" :key="index">{{ err }}</li>
      </ul>
    </div>

    <!-- Erreur générique -->
    <p v-if="errorMessage && validationErrors.length === 0" class="state-message state-message--error">
      {{ errorMessage }}
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { importProducts } from '@/services/productApi'

const loading = ref(false)
const selectedFile = ref(null)
const successMessage = ref('')
const errorMessage = ref('')
const validationErrors = ref([])
const warnings = ref([])

function handleFileChange(event) {
  selectedFile.value = event.target.files[0]
  successMessage.value = ''
  errorMessage.value = ''
  warnings.value = []
  validationErrors.value = []
}

async function handleImport() {
  if (!selectedFile.value) return

  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''
  validationErrors.value = []
  warnings.value = []

  try {
    const result = await importProducts(selectedFile.value)
    successMessage.value = result.message
    warnings.value = result.details ?? []
    selectedFile.value = null
  } catch (error) {
    errorMessage.value = error.message
    validationErrors.value = error.cause ?? []
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Styles partagés avec ExportSection */
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

.export-icon { font-size: 2rem; }

.export-label {
  font-weight: 500;
  color: var(--color-text-dark);
  font-size: 0.95rem;
}

.export-hint {
  font-size: 0.8rem;
  color: var(--color-text-light);
  margin-top: 0.2rem;
}

/* Spécifique import */
.btn-label {
  display: inline-flex;
  cursor: pointer;
}

.btn-valider {
  flex: 0;
}

.btn-label input[type="file"] {
  display: none;
}

.btn-disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

.file-selected {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1rem;
  padding: 1rem 1.5rem;
  background: var(--color-bg-soft);
  border-radius: var(--radius-card);
  border: 1.5px solid var(--color-border);
}

.file-selected p {
  color: var(--color-text-dark);
  font-size: 0.9rem;
}

.validation-errors {
  margin-top: 1rem;
  padding: 1rem 1.5rem;
  background: var(--color-danger-bg);
  border-radius: var(--radius-card);
  border: 1.5px solid var(--color-danger);
}

.error-title {
  color: var(--color-danger-text);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.validation-errors ul { padding-left: 1.2rem; }

.validation-errors li {
  color: var(--color-danger-text);
  font-size: 0.85rem;
  margin-bottom: 0.25rem;
}
</style>
