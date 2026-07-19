import { apiFetch } from './client.js';

/**
 * Récupère la liste des régimes alimentaires.
 */
export async function getDiets() {

    const response = await apiFetch('/api/diets');

    return response.json();

}