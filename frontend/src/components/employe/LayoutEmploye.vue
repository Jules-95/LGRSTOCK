<template>
  <div class="layout">
    <AppTopbar />

    <main class="page">
      <div class="container">
        <div v-if="backLabel" class="back-button">
          <button class="btn-back" @click="router.push('/')">
            {{ backLabel }}
          </button>
        </div>
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import AppTopbar from "@/components/employe/AppTopbar.vue";

const router = useRouter();

defineProps({
  backLabel: {
    type: String,
    default: null,
  },
});
</script>

<style scoped>
.layout {
  min-height: 100vh;
  background: var(--color-bg-light);
}

.page {
  padding: 2rem 1.5rem;
}

.container {
  max-width: 600px;
  margin: 0 auto;
}

.back-button {
  margin-bottom: 1.5rem;
}

.btn-back {
  padding: 0.5rem 1rem;
  background: white;
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius-btn);
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  color: var(--color-text-medium);
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}
</style>
