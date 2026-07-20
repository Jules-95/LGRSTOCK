import { API_BASE_URL } from "@/config";

/**
 * Recherche de produits selon des critères
 * @param {Object} filters - { ean, libelle, fournisseur }
 * @returns {Promise<Array>} Liste des produits trouvés
 * @throws {Error} Si erreur API
 */

export async function searchProducts(filters, page = 1) {
  // Construire les paramètres URL (enlever les valeurs vides)
  const params = new URLSearchParams();
  if (filters.ean) params.append("ean", filters.ean);
  if (filters.libelle) params.append("libelle", filters.libelle);
  if (filters.fournisseur) params.append("fournisseur", filters.fournisseur);
  params.append("page", page);

  // Appel API
  const response = await fetch(
    `${API_BASE_URL}/Product/search.php?${params.toString()}`,
    {
      credentials: "include",
    },
  );

  if (response.status >= 500) {
    throw new Error(`Erreur serveur : ${response.status}`);
  }
  const data = await response.json();

  // Gestion des erreurs
  if (data.error) {
    throw new Error(data.message);
  }
  // Retour des données
  return data;
}

/**
 * Récupérer un produit par son ID
 * @param {number} id - ID du produit
 * @returns {Promise<Object>} Le produit trouvé
 * @throws {Error} Si erreur API
 */

export async function getProductById(id) {
  const response = await fetch(`${API_BASE_URL}/Product/product.php?id=${id}`, {
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const data = await response.json();

  if (data.error) {
    throw new Error(data.message);
  }

  return data.data;
}

/**
 * Ajouter un nouveau produit
 * @param {object} data - {libelle, ean, fournisseur, quantite}
 * @returns {Promise<Object>} L'ID du produit créé
 * @throws {Error} Si erreur API
 */
export async function addProduct(data) {
  const response = await fetch(`${API_BASE_URL}/Product/add-product.php`, {
    method: "POST",
    credentials: "include",
    body: new URLSearchParams(data),
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result;
}

/**
 * Supprimer un produit existant
 * @param {number} id - L'ID du produit à supprimer
 * @throws {Error} Si erreur API
 */
export async function deleteProduct(id) {
  const response = await fetch(`${API_BASE_URL}/Product/delete-product.php`, {
    method: "POST",
    credentials: "include",
    body: new URLSearchParams({ id }),
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result;
}

/**
 * @param {number} id - L'ID du produit à modifier
 * @param {object} data - { libelle, ean, fournisseur, quantite }
 * @returns {Promise<Object>}
 * @throws {Error} Si erreur API
 */
export async function editProduct(id, data) {
  const response = await fetch(`${API_BASE_URL}/Product/edit-product.php`, {
    method: "POST",
    credentials: "include",
    body: new URLSearchParams({ id, ...data }),
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result;
}

/**
 * Exporte tous les produits en fichier CSV et déclenche le téléchargement
 * @returns {Promise<void>}
 * @throws {Error} Si erreur API
 */
export async function exportProducts() {
  const response = await fetch(`${API_BASE_URL}/export.php`, {
    method: "GET",
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  if (!response.ok) {
    const result = await response.json();
    throw new Error(result.message);
  }

  const blob = await response.blob();
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");

  link.href = url;
  link.download = `stock_lgr_${new Date().toISOString().slice(0, 10)}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Importe des produits depuis un fichier CSV
 * @param {File} file - Le fichier CSV sélectionné par l'utilisateur
 * @returns {Promise<Object>} - { message, details }
 * @throws {Error} Si erreur API ou validation
 */
export async function importProducts(file) {
  const formData = new FormData();
  formData.append("csv", file);

  const response = await fetch(`${API_BASE_URL}/import.php`, {
    method: "POST",
    credentials: "include",
    body: formData,
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message, { cause: result.details });
  }

  return result;
}

/**
 * Récupère les statistiques globales pour le dashboard admin
 * @returns {Promise<Object>} { total_produits, produits_rupture, total_utilisateurs }
 * @throws {Error} Si erreur API
 */
export async function getStats() {
  const response = await fetch(`${API_BASE_URL}/stats.php`, {
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const data = await response.json();

  if (data.error) {
    throw new Error(data.message);
  }

  return data.data;
}
