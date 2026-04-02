import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { checkAuth } from '@/services/api'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Page de login - Seule route accessible sans être connecté
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true, employeOnly: true }
    },
    // Import dynamique - ProductDetail chargé uniquement quand l'utilisateur navigue vers /product/:id
    {
      path: '/product/:id',
      name: 'product-detail',
      component: () => import('../views/ProductDetail.vue'),
      meta: { requiresAuth: true, employeOnly: true }
    },
    // Dashboard admin - accessible uniquement si connecté et role admin
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    // Vue Ajouter un produit - admin only
    {
      path: '/ajouter-produit',
      name: 'add-product',
      component: () => import('../views/AddProductView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true }
    },
    // Dernière route : 404 Not Found
    // Vue Router parcourt les routes dans l'ordre et s'arrête à la première qui correspond
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue')
    }
  ]
})

/**
 * Navigation guard - Exécuté avant chaque changement de route
 * Vérifie les droits d'accès selon les meta de la route cible
 *
 * meta.requiresAuth  -> Doit être connecté
 * meta.requiresAdmin -> Doit être connecté et Admin
 * meta.employeOnly   -> Réservé aux employés, admin redirigé vers /admin
 * meta.guestOnly     -> Redirige si déjà connecté
 */
router.beforeEach(async (to) => {
  if (to.meta.requiresAuth) {
    const user = await checkAuth()

    // Pas de session active -> Login
    if (!user) {
      return { name: 'login' }
    }

    // Route réservée aux employés -> admin redirigé vers /admin
    if (to.meta.employeOnly && user.role === 'admin') {
      return { name: 'admin' }
    }

    // Connecté mais pas admin sur une route admin -> Home
    if (to.meta.requiresAdmin && user.role !== 'admin') {
      return { name: 'home' }
    }
  }

  // Déjà connecté -> redirige vers l'espace adapté au lieu d'afficher /login
  if (to.meta.guestOnly) {
    const user = await checkAuth()
    if (user) {
      return user.role === 'admin' ? { name: 'admin' } : { name: 'home' }
    }
  }
})

export default router