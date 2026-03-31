/** 
 * Service API - Centralise les appels au backend 
 * 
 * Rôle : Comme un Product.php en backend, gère la logique d'appels API 
 */

import { API_BASE_URL } from "@/config";

/**  
 * Recherche de produits selon des critères 
 * @param {Object} filters - { ean, libelle, fournisseur }
 * @returns {Promise<Array>} Liste des produits trouvés
 * @throws {Error} Si erreur API
 */

export async function searchProducts(filters) {
    // Construire les paramètres URL (enlever les valeurs vides)
    const params = new URLSearchParams()
    if (filters.ean) params.append('ean', filters.ean)
    if (filters.libelle) params.append('libelle', filters.libelle)
    if (filters.fournisseur) params.append('fournisseur', filters.fournisseur)

    // Appel API
    const response = await fetch(`${API_BASE_URL}/search.php?${params.toString()}`)

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const data = await response.json()

    // Gestion des erreurs
    if (data.error) {
        throw new Error(data.message)
    }
    // Retour des données
    return data
}

/** 
 * Récupérer un produit par son ID 
 * @param {number} id - ID du produit 
 * @returns {Promise<Object>} Le produit trouvé
 * @throws {Error} Si erreur API
*/

export async function getProductById(id) {
    const response = await fetch(`${API_BASE_URL}/product.php?id=${id}`)

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const data = await response.json()

    if (data.error) {
        throw new Error(data.message)
    }
    
    return data.data
}


/** 
 * Ajouter un nouveau produit 
 * @param {object} data - {libelle, ean, fournisseur, quantite}
 * @returns {Promise<Object>} L'ID du produit créé
 * @throws {Error} Si erreur API
*/
export async function addProduct(data) {
    const response = await fetch(`${API_BASE_URL}/add-product.php`, {
        method: 'POST',
        body: new URLSearchParams(data)
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const result = await response.json()

    if (result.error) {
        throw new Error(result.message)
    }

    return result
    
}


/**
 * Supprimer un produit existant
 * @param {number} id - L'ID du produit à supprimer
 * @throws {Error} Si erreur API
 */
export async function deleteProduct(id) {
    const response = await fetch (`${API_BASE_URL}/delete-product.php`, {
        method: 'POST',
        body: new URLSearchParams({ id })
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const result = await response.json()

    if (result.error) {
        throw new Error(result.message)
    }

    return result
    
}



/** 
 * Met à jour la quantite d'un produit 
 * @param {number} id - L'ID du produit 
 * @param {number} quantite - La nouvelle quantité
 */
export async function updateStock(id, quantite) {
    const response = await fetch (`${API_BASE_URL}/update-stock.php` , {
        method: 'POST',
        body: new URLSearchParams({ id, quantite})
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const data = await response.json()

    if (data.error) {
        throw new Error(data.message)
    }

    return data
}