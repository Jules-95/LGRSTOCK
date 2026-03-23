import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    // NOUVELLE ROUTE => Import dynamique (ProductDetail chargé uniquement quand l'utilisateur navigue vers /product/:id
    {
      path: '/product/:id',
      name: 'product-detail',
      component: () => import('../views/ProductDetail.vue')
    },
    // NOUVELLE ROUTE : Vue Ajouter un produit 
    {
      path: '/ajouter-produit',
      name: 'add-product',
      component: () => import('../views/AddProductView.vue')
    },
    // NOUVELLE ROUTE (Dernière): 404 Not Found
    {
      path: '/:pathMatch(.*)*', // Capture toutes les urls qui ne correspondent à aucune route route définie -> Vue Router parcours les routes dans l'ordre et s'arrête à la première qui correspond. 
      name: 'not-found',
      component: () => import('../views/NotFoundView.vue')
    }
  ]
})

export default router