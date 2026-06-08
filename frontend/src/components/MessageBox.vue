<!-- Responsabilité de MessageBox = Afficher les messages avec le style correspondant -->

<template>
  <div class="message" :class="type">{{ icon }} {{ message }}</div>
</template>

<script setup>
import { computed } from "vue";

// Props = Communication Parent -> enfant
const props = defineProps({
  type: String, // Le parent DOIT envoyer une string
  message: String,
});

// Icône selon le type de message
const icon = computed(() => {
  if (props.type === "error") return "❌";
  if (props.type === "info") return "ℹ️";
  if (props.type === "loading") return "⏳";
  return "";
});
</script>

<style scoped>
/* Messages d'état */

.message {
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: var(--radius-btn);
  margin-top: 1.5rem;
  font-weight: 500;
}

.message.info {
  background: #dbeafe;
  color: #1e40af;
}

.message.error {
  background: var(--color-danger-bg);
  color: var(--color-danger-text);
}

.message.loading {
  background: var(--color-bg-light);
  color: var(--color-text-medium);
}

@media (max-width: 1000px) {
  .message {
    padding: 0.6rem 0.75rem;
    font-size: 0.85rem;
    margin-top: 1rem;
  }
}
</style>
