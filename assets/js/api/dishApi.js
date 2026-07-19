import { apiFetch } from './client.js';

/**
 * Récupère la liste des plats.
 */
export async function getDishes() {

    const response = await apiFetch('/api/dishes');

    return response.json();

}