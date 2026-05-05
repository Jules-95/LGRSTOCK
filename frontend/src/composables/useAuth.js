/**
 * ============================================================
 * COMPOSABLE : useAuth
 * ============================================================
 *
 * CE QUE C'EST :
 * Un composable est une fonction réutilisable qui centralise
 * un état partagé entre plusieurs vues. Ici il centralise
 * l'état de connexion — qui est connecté, quel est son rôle.
 *
 * CE QU'IL REÇOIT :
 * - Les données retournées par checkAuth() et login()
 *
 * CE QU'IL FAIT :
 * - Stock les infos de l'utilisateur connecté (user, role)
 * - Expose isAdmin pour conditionner l'affichage
 * - Fournit login() et logout() aux vues
 *
 * CE QU'IL DESSERT :
 * - LoginView.vue  → appelle login()
 * - HomeView.vue   → vérifie isAdmin pour afficher/cacher des éléments
 * - AdminView.vue  → vérifie isAdmin
 * - router/index.js → appelle checkAuth() pour les guards
 * ============================================================
 */

import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout, checkAuth} from '@/services/authApi'
import { useRouter } from 'vue-router'

// Ces variables sont définies EN DEHORS de la fonction 
// -> Elles sont partagées entre toutes les vues qui utilsent useAuth
const user = ref(null) // { username, role, magasin } ou null 

export function useAuth() {
    const router = useRouter()

    // true si l'utilisateur connecté est admin 
    const isAdmin = computed(() => user.value?.role === 'admin')

    //true si quelqu'un est connecté 
    const isAuthenticated = computed(() => user.value !==null)

    /**
     * Initialise l'état auth au démarage de l'app
     * Appelé une fois dans App.vue
     */
    async function init() {
        const data = await checkAuth()
        user.value = data // null si pas connecté, sinon { role, username, magasin }
    }
    
    /**
     * Connecte l'utilisateur et redirige selon son rôle
     */
    async function login(username, password) {
        const data = await apiLogin(username, password)
        user.value = data

        if (data.role === 'admin') {
            router.push('/admin')
        } else {
            router.push('/')
        }
    }
    
    /**
     * Déconnecte l'utilisateur et redirige vers /login
     */
    async function logout() {
        await apiLogout()
        user.value = null
        router.push('/login')
    }

    return {
        user,
        isAdmin,
        isAuthenticated,
        init,
        login,
        logout
    }
}