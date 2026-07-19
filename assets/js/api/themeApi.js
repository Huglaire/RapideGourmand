import { apiFetch } from './client.js';

/**
 * Récupère la liste des thèmes.
 */
export async function getThemes() {

    const response = await apiFetch('/api/themes');

    return response.json();

}