<template>
    <div class="home">
        <div class="container">

            <header class="header">
                <h1 class="logo">LGR STOCK</h1>
                <p class="subtitle">Outil de visualisation et de manipulation de stock de la reserve Colombe</p>
            </header>

            <div class="card-item">
                <h2>🔍 Recherche de produits</h2>

                <form @submit.prevent="handleSearch">
                    <div class="form-group">
                        <label>Code EAN</label>
                        <input 
                            v-model="searchEAN"
                            type="text"
                            placeholder="3700523456789"
                            />
                    </div>

                    <div class="form-group">
                        <label>Libellé du produit</label>
                        <input
                            v-model="searchLibelle"
                            type="text"
                            placeholder="Ex: Flip 7, Lego..."
                            />
                    </div>

                    <div class="form-group">
                        <label>Fournisseur</label>
                        <input
                            v-model="searchFournisseur"
                            type="text"
                            placeholder="Ex: Mattel, Blackrock..."
                            />
                    </div>

                    <button class="search-btn" type="submit">🔍 Rechercher</button>
                </form>

            </div>
        </div>
    </div>
</template>



<script setup>
import { ref } from 'vue'

// Variables réactives pour stocker ce que l'utilisateur entre dans un champ
const searchEAN = ref('')
const searchLibelle = ref('')
const searchFournisseur = ref('')

// Nouvelle variables pour gérer l'état de la recherche (connexion API)
const products = ref([])    // Liste des produits trouvés
const loading = ref(false)  // Indique si une recherche est en cours
const error = ref(null)     // Message d'erreur éventuel
const searched = ref(false) // Indique si un erecherche a été lancée 

// Fonction appelé au clique sur le btn "Rechercher"
async function handleSearch() {
    // Réinitialiser l'état
    error.value = null
    products.value = []
    searched.value = true

    // Vérifier qu'au moins un champ est rempli
    if (!searchEAN.value && !searchLibelle.value && !searchFournisseur.value) {
    error.value = 'Veuillez remplir au moins un champ de recherche'
    return
    }

    // Construire l'URL de l'API avec les paramètres
    const params = new URLSearchParams()
    if (searchEAN.value) params.append('ean', searchEAN.value)
    if (searchLibelle.value) params.append('libelle', searchLibelle.value)
    if (searchFournisseur.value) params.append('fournisseur', searchFournisseur.value)

    const apiURL = `http://localhost/LGRSTOCK/backend/api/search.php?${params.toString()}`

    console.log('URL appelée :', apiURL)

    loading.value = true

    try {
    const response = await fetch(apiURL)
    const data = await response.json()
    
    console.log('Réponse API :', data)
    
    if (data.error) {
      error.value = data.message
    } else {
      products.value = data.data
    }
    
    } catch (err) {
    console.error('Erreur :', err)
    error.value = 'Erreur lors de la recherche. Vérifiez que le backend est actif.'
    } finally {
    loading.value = false
    }

}
</script>



<style scoped>

/* Style qui sera commun (UTILISATION DE COMPOSANT ?) */

/* Mis en page de toutes les vues  */
.home {
    min-height: 100vh;
    padding: 2rem;
}

.container {
    max-width: 800px;
    margin: 0 auto;
}
/* Header commun à toutes les vues */
.header {
    text-align: center;
    margin-bottom: 2rem;
}

.logo {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin-bottom: 0.5rem;
}

.subtitle {
    color: white;
    font-size: 1.1rem;
}

/* Style des cards conteneurs */
.card-item {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.card-item h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: #1f2937;
}


/* Style des champs formulaire */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-group input {
    width: 100%;
    padding: 1rem;
    border : 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
}

/* --- BOUTON --- */

.search-btn {
    padding: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
}

</style>



