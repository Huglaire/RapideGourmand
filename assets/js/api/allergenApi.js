import { apiFetch } from './client.js';

/**
 * Retourne tous les allergènes.
 */
export async function getAllergens() {

    const response = await apiFetch('/api/allergens');

    return await response.json();

}