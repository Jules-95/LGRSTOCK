<template>
    <main class="page">
        <div class="container">

            <!-- Bouton retour affiché uniquement si backLabel est fourni-->
             <div v-if="backLabel" class="back-button">
                <button class="btn-back" @click="router.push('/')">
                    {{ backLabel }}
                </button>
             </div>

             <!-- Slot : Le contenu de la vue parente arrive ici-->
              <slot />

        </div>
    </main>
</template>


<script setup>
import { useRouter } from 'vue-router';

const router = useRouter()

defineProps({
    //Label du btn retour - si Absent, bouton non affiché
    backLabel : {
        type: String,
        default: null
    }
})
</script>

<style scoped>

.page {
    min-height: 100vh;
    padding: 2rem;
    padding-top: 3rem;
}

.container {
    max-width: 800px;
    margin: 0 auto;
}

.back-button {
    margin-bottom: 1.5rem;
}

.btn-back {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px solid var(--color-border);
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-back:hover {
  border-color: var(--color-primary);
  transform: translateX(-3px);
}

</style>