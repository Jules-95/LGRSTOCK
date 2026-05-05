/** 
 * Service API - Centralise les appels au backend 
 * 
 * Rôle : Comme un Product.php en backend, gère la logique d'appels API 
 */

import { API_BASE_URL } from "@/config";


// ============================================================
// UTILISATEURS
// ============================================================

 /**
  * Récupérer tous les utilisateurs
  * @returns {Promise<Array>} Liste des utilisateurs
  * @throws {Error} si erreur API
  */

 export async function getUsers() {
    const response = await fetch(`${API_BASE_URL}/User/users.php`, {
        credentials: 'include'
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const data = await response.json()

    if (data.error) {
        throw new Error(data.message)
    }

    return data
 }

 /**
  * Ajouter un nouvel utilisateur 
  * @param {object} data - { username, password, role, magasin }
  * @returns {Promise<Object>} L'ID de l'utilisateur créé
  * @throws {Error} si erreur API
  */
export async function addUser(data) {
    const response = await fetch(`${API_BASE_URL}/User/add-user.php`, {
        method: 'POST',
        credentials: 'include',
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
 * Modifier un utilisateur existant 
 * @param {number} id - L'ID de l'utilisateur à modifier
 * @param {object} data - { username, password, role, magasin }
 * @returns {promise<Object>}
 * @throws {Error} si Erreur API 
 */
export async function editUser(id, data) {
    const response = await fetch(`${API_BASE_URL}/User/edit-user.php`, {
        method: 'POST',
        credentials: 'include',
        body: new URLSearchParams({ id, ...data })
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)
    
    const result = await response.json()

    if (result.error) {
        throw new Error(result.message)
    }

    return result
}

/**
 * Supprimer un utilisateur
 * @param {number} id - L'ID de l'utilisateur 
 * @throws {error} Si erreur API 
 */
export async function deleteUser(id) {
    const response = await fetch (`${API_BASE_URL}/User/delete-user.php`, {
        method: 'POST',
        credentials: 'include',
        body: new URLSearchParams({ id })
    })

    if (response.status >= 500) throw new Error(`Erreur serveur: ${response.status}`)

    const result = await response.json()

    if (result.error) {
        throw new Error (result.message)
    }

    return result

}