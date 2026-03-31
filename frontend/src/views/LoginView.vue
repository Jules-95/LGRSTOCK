<template>
    <PageLayout>
        <AppCard title="Connexion">
            <form class="login-form" @submit.prevent="handleLogin">
                
                <div class="field">
                    <label for="username">Nom d'utilisateur</label>
                    <input 
                    id="username"
                    v-model="username"
                    type="text"
                    placeholder="Votre identifiant"
                    autocomplete="username"
                    />
                </div>

                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input
                    id="password"
                    v-model="password"
                    type="password"
                    placeholder="Votre mot de passe"
                    autocomplete="current-password"
                    />
                </div>

                <MessageBox
                v-if="errorMessage"
                type="error"
                :message="errorMessage"
                />

                <button class="btn-login" type="submit" :disabled="loading">
                    {{ loading ? 'Connexion...' : 'Se connecter' }}
                </button>

            </form>
        </AppCard>
    </PageLayout>
</template>

<script setup>
import { ref } from'vue'
import AppCard from '@/components/AppCard.vue';
import MessageBox from '@/components/MessageBox.vue';
import PageLayout from '@/components/PageLayout.vue';
import { useAuth } from '@/composables/useAuth';

const { login } = useAuth()

const username          = ref('')
const password          = ref('')
const errorMessage      = ref('')
const loading           = ref(false)     

async function handleLogin() {
    errorMessage.value = ''
    loading.value = true

    try {
        await login(username.value, password.value)
        // La redirection est gérée dans useAuth.login()
        // Selon le role -> admin : /admin, employé : /
    } catch (error) {
        errorMessage.value = error.message
    } finally {
        loading.value = false
    }
}

</script>

<style scoped>

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.field input {
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-input);
    font-size: 1rem;
    color: var(--color-text-dark);
    background: var(--color-bg-soft);
    outline: none;
    transition: border-color 0.2s;
}

.field input:focus {
    border-color: var(--color-primary);
}

.btn-login {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
    color: white;
    border: none;
    border-radius: var(--radius-btn);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition : opacity 0.2s;
}

.btn-login:hover {
    opacity: 0.9;
}

.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

</style>