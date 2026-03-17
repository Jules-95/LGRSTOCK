import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import AddProductView from '@/views/AddProductView.vue'

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
    }
  ]
})

export default router