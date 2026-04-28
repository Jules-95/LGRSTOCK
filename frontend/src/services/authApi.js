import { API_BASE_URL } from "@/config";

/**
 * Connecter un utilisateur 
 * @param {string} username
 * @param {string} password
 * @returns {Promise<object>} - { role, magasin, username }
 * @throws {Error} Si identifiants incorrects
 */
export async function login(username, password) {
    const response = await fetch(`${API_BASE_URL}/Auth/login.php`, {
        method: 'POST', 
        credentials: 'include',
        body: new URLSearchParams({ username, password})
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)
    
    const data = await response.json() 

    if (data.error) {
        throw new Error(data.message)
    }

    return data
}

/**
 * Déconnecter l'utilisateur
 * @throws {Error} si erreur serveur
 */
export async function logout() {
    const response = await fetch (`${API_BASE_URL}/Auth/logout.php`, {
        method: 'POST',
        credentials: 'include'
    })

    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    return await response.json()
}

/**
 * Verifier si une session est active 
 * Appelé au démarage de l'app et dans les guards du router
 * @returns {promise<Object|null>} - { role, magasin, username } ou null
 */
export async function checkAuth() {
    const response = await fetch(`${API_BASE_URL}/Auth/check-auth.php`, {
        credentials: 'include'
    })

    if (response.status === 401) return null
    if (response.status >= 500) throw new Error(`Erreur serveur : ${response.status}`)

    const data = await response.json()
    return data.error ? null : data
}