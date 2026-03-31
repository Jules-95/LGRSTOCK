<template>
  <PageLayout>
    <AppCard>
      <div class="not-found">
        <h1>404</h1>
        <p>Cette page n'existe pas.</p>
        <button class="btn-retour" @click="router.push('/')">
          Retour à l'accueil
        </button>
      </div>
    </AppCard>
  </PageLayout>
</template>

<script setup>
import { useRouter } from 'vue-router'
import PageLayout from '@/components/PageLayout.vue'
import AppCard from '@/components/AppCard.vue'

const router = useRouter()
</script>

<style scoped>
.not-found {
  text-align: center;
  padding: 2rem 0;
}

.not-found h1 {
  font-size: 4rem;
  font-weight: 800;
  color: var(--color-primary);
  margin-bottom: 1rem;
}

.not-found p {
  color: var(--color-text-light);
  font-size: 1.1rem;
  margin-bottom: 2rem;
}

.btn-retour {
  padding: 0.75rem 2rem;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: var(--radius-btn);
  font-weight: 600;
  cursor: pointer;
}

.btn-retour:hover {
  background: var(--color-primary-dark);
}
</style>