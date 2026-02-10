import { createApp } from 'vue'     // Importe la fonction pour créer une appVUE
import App from './App.vue'         //Importe le composant racine
import router from './router/index' // Importe la configuration des routes

const app = createApp(App)      // Crée l'instance Vue avec App.vue comme base

app.use(router)                 // Active le système de navigation (Vue Router)

app.mount('#app')               // Accroche Vue sur <div id="app"> dans index.html
