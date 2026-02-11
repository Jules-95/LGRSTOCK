<template>
  <div class="home">
    <div class="container">
      <header class="header">
        <h1 class="logo">LGR STOCK</h1>
        <p class="subtitle">Site interne de visualition du stock de la réserve Colombe</p>
      </header>

      <div class="search-card">
        <h2>🔍 Recherche de produits</h2>
        <p style="color: #6b7280; margin-bottom: 2rem;">
          Recherchez un produit par code EAN, libellé ou fournisseur
        </p>

        <!-- Formulaire de recherche -->
        <form @submit.prevent="handleSearch"> 
            <!-- 
            @submit = écoute l'évènement "soumission du formulaire"
            Prevent = equivalent de event.preventDefault() qui empêche le rechargement de page) 
            handleSearch = fonction appelée lors du clic sur "Rechercher"
            -->
          <div class="form-group">
            <label>Code EAN</label>
            <input 
              v-model="searchEAN" 
              type="text" 
              placeholder="3700523456789"
              maxlength="13"
            />
          </div>

          <div class="form-group">
            <label>Libellé du produit</label>
            <input 
              v-model="searchLibelle" 
              type="text" 
              placeholder="Ex: Lego, Barbie..."
            />
          </div>

          <div class="form-group">
            <label>Fournisseur</label>
            <input 
              v-model="searchFournisseur" 
              type="text" 
              placeholder="Ex: LEGO Group, Mattel..."
            />
          </div>
          <!-- v-model = Liaison bidirectionnelle (two-way-binding) -> Ce que l'utilisateur tape est automatiquement stocké dans searchEAN -->

          <button type="submit" class="btn-search">
            🔍 Rechercher
          </button>
        </form>

        <!-- Messages d'état -->
        <div v-if="loading" class="message info">
          ⏳ Recherche en cours...
        </div>

        <div v-if="error" class="message error">
          ❌ {{ error }}
        </div>

        <!-- Résultats -->
        <div v-if="products.length > 0" class="results">
          <h3>📦 Résultats ({{ products.length }})</h3>

          <!-- v-if = Affichage conditionnel (comme un if en javascript)
           si loading === true ALORS ce bloc s'affiche-->
          
          <div class="product-list">
            <div 
              v-for="product in products" 
              :key="product.id"
              class="product-item"
            >
            <!-- v-for = boucle (comme un for en js) 
             Crée une div pour chaque produit du tableai
             :key = identifiant unique pour optimiser les mises à jour DOM -->

              <div class="product-info">
                <h4>{{ product.libelle }}</h4>
                <p>EAN : {{ product.ean }}</p>
                <p>Fournisseur : {{ product.fournisseur || 'Non renseigné' }}</p>
                <p class="stock">Stock : {{ product.quantite }} unités</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Aucun résultat -->
        <div v-if="!loading && searched && products.length === 0" class="message info">
          ℹ️ Aucun produit trouvé
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// États réactifs
const searchEAN = ref('')
const searchLibelle = ref('')
const searchFournisseur = ref('')
const products = ref([])
const loading = ref(false)
const error = ref(null)
const searched = ref(false)
// Décalartion des variables réactives
// ref () 
    //Transforme une valeur normale en valeur réactive
    // Vue surveille automatiquement les changements
    // Pour lire/modifier -> Utiliser .value
    //Pourquoi pas "let" ? 
        // let ne déclenche pas de mise à jour de l'interface, ref rend la variable "réactive"

// Fonction de recherche
async function handleSearch() {
  // Réinitialiser
  error.value = null
  products.value = []
  searched.value = true
// Pourquoi une fonction async ? Car permet d'utiliser await pour attendre les réponses API, evite de bloquer l'interface pendant le chargement.
// Réinitialisation = Efface les anciens messages d'erreur, vide la liste de produit précédente, indique qu'une recherche a été lancée (pour afficher "Aucun résulat")
  
  // Vérifier qu'au moins un champ est rempli
  if (!searchEAN.value && !searchLibelle.value && !searchFournisseur.value) {
    error.value = 'Veuillez remplir au moins un champ de recherche'
    return
  }
  // Evite d'appeller l'API si aucun champ n'est rempli. (return : arrête la fonction ici)
  
  // Construire l'URL de l'API
  const params = new URLSearchParams()
  if (searchEAN.value) params.append('ean', searchEAN.value)
  if (searchLibelle.value) params.append('libelle', searchLibelle.value)
  if (searchFournisseur.value) params.append('fournisseur', searchFournisseur.value)
  // URLSearchParams() : Crée une chaine de carctère GET
  // params.append : ('ean', 123) -> ajoute `?ean=123` à l'url 

  const apiURL = `http://localhost/LGRSTOCK/backend/api/search.php?${params.toString()}`
  
  // Afficher l'URL dans la console (pour déboguer)
  console.log('Appel API :', apiURL)
  
  loading.value = true
  
  try {
    const response = await fetch(apiURL)    
    const data = await response.json()
    // fetch : envoie une requête HTTP GET vers PHP
    // await : attend la réponse du serveur 
    // response.json: transforme le JSON en objet JavaScript
    
    
    console.log('Réponse API :', data)

    
    if (data.error) {
      error.value = data.message
    } else {
      products.value = data.data
    }
    // si erreur coté backend -> affiche message d'erreur 
    // Sinon -> Remplit le tableau product (Vue met à jours l'affichage automatiquement)
    
  } catch (err) {
    console.error('Erreur :', err)
    error.value = 'Erreur lors de la recherche. Vérifiez que le backend est actif (XAMPP...).'
  } finally {
    loading.value = false
  }
  // try/catch -> capture les erreurs réseau (backend eteint, timout)
  // finally -> exécuté dans tous les cas -> Arrête le spinner de chargement (succès ou erreur)
}
</script>

<style scoped>
.home {
  min-height: 100vh;
  padding: 2rem;
}

.container {
  max-width: 800px;
  margin: 0 auto;
}

.header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  font-size: 3rem;
  font-weight: 800;
  color: white;
  text-shadow: 0 4px 20px rgba(0,0,0,0.2);
  margin-bottom: 0.5rem;
  letter-spacing: 2px;
}

.subtitle {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1.1rem;
  font-weight: 300;
}

.search-card {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.search-card h2 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  color: #1f2937;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-group input {
  width: 100%;
  padding: 1rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-search {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-search:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.message {
  padding: 1rem;
  border-radius: 10px;
  margin-top: 1.5rem;
  font-weight: 500;
}

.message.info {
  background: #dbeafe;
  color: #1e40af;
}

.message.error {
  background: #fee2e2;
  color: #991b1b;
}

.results {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
}

.results h3 {
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.product-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  transition: all 0.2s;
}

.product-item:hover {
  border-color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.product-info h4 {
  color: #1f2937;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.product-info p {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.product-info .stock {
  color: #667eea;
  font-weight: 600;
  margin-top: 0.5rem;
}
</style>
