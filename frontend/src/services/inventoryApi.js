import { API_BASE_URL } from "@/config";

/**
 * Récupère l'inventaire en cours du dépôt de l'admin connecté
 * @returns {Promise<Object|null>} { id, depot, started_at, started_by } ou null si aucun
 * @throws {Error} Si erreur API
 */

export async function getInventoryStatus() {
  const response = await fetch(`${API_BASE_URL}/Inventory/status.php`, {
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result.data;
}

/**
 * Démarer un inventaire : remet à 0 le stock du dépôt de l'admin connecté
 * @returns {Promise<Object>} L'inventaire créé
 * @throws {Error} Si erreur API (ex: Invenaire déja en cours)
 */

export async function startInventory() {
  const response = await fetch(`${API_BASE_URL}/Inventory/start.php`, {
    method: "POST",
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result.data;
}

/**
 * Termine l'inventaire en cours du dépôt de l'admin connecté
 * @throws {Error} si erreur API (ex : aucun inventaire en cours)
 */

export async function finishInventory() {
  const response = await fetch(`${API_BASE_URL}/Inventory/finish.php`, {
    method: "POST",
    credentials: "include",
  });

  if (response.status >= 500)
    throw new Error(`Erreur serveur : ${response.status}`);

  const result = await response.json();

  if (result.error) {
    throw new Error(result.message);
  }

  return result;
}
