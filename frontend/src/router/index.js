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
      meta: { guestOnly: true} // redirige vers l'espace adapté si déjà connecté
    },

    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }
    },

    // NOUVELLE ROUTE => Import dynamique (ProductDetail chargé uniquement quand l'utilisateur navigue vers /product/:id
    {
      path: '/product/:id',
      name: 'product-detail',
      component: () => import('../views/ProductDetail.vue'),
      meta: { requiresAuth: true }
    },

    // Dashboard admin - accessible uiquement si connecté et role admin
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminView.vue'),
      meta: {requiresAuth: true, requiresAdmin: true }
    },

    // NOUVELLE ROUTE : Vue Ajouter un produit 
    {
      path: '/ajouter-produit',
      name: 'add-product',
      component: () => import('../views/AddProductView.vue'),
      meta: {requiresAuth: true, requiresAdmin: true }
    },

    // Dernière route : 404 Not Found
    // Vue Router parcours les routes dans l'ordre et s'arrête à la première qui correspond. 
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
 * meta.requiresAuth  -> Doit etre connecté
 * meta.requiresAdmin -> Doit etre connecté et Admin
 * meta.guestOnly     -> Redirige si déja connecté 
 */
router.beforeEach(async (to) => {
  if (to.meta.requiresAuth) {
    const user = await checkAuth()

    // Pas de session active -> Login
    if (!user) {
      return { name: 'login' }
    }

    // Connecté mais pas admin sur une route admin -> Home
    if (to.meta.requiresAdmin && user.role !== 'admin') {
      return { name: 'home'}
    }
  }

  // Deja connecté -> Redirige vers l'espace adapté au lieu d'afficher /login
  if (to.meta.guestOnly) {
    const user = await checkAuth()
    if (user) {
      return user.role === 'admin' ? { name: 'admin' } : { name: 'home'}
    }
  }
})

export default router