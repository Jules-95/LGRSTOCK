<template>
  <div v-if="totalPages > 1" class="pagination">
    <button :disabled="currentPage === 1" @click="$emit('change', currentPage - 1)">
      &lt;
    </button>

    <button
      v-for="p in visiblePages"
      :key="p"
      :class="{ active: p === currentPage }"
      @click="$emit('change', p)"
    >
      {{ p }}
    </button>

    <button :disabled="currentPage === totalPages" @click="$emit('change', currentPage + 1)">
      &gt;
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ currentPage: Number, totalPages: Number })
defineEmits(['change'])

const visiblePages = computed(() => {
  if (props.totalPages <= 3) {
    return Array.from({ length: props.totalPages }, (_, i) => i + 1)
  }
  const start = Math.max(1, Math.min(props.currentPage - 1, props.totalPages - 2))
  return [start, start + 1, start + 2]
})
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
}

.pagination button {
  padding: 0.5rem 0.85rem;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-btn);
  background: white;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.pagination button.active {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.pagination button:hover:not(:disabled):not(.active) {
  border-color: var(--color-primary);
}
</style>
