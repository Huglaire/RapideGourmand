import { apiFetch } from './client.js';

/**
 * Retourne toutes les images.
 */
export async function getPictures() {

    const response = await apiFetch('/api/pictures');

    return await response.json();

}