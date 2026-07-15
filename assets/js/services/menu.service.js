import { apiFetch } from '../api/client.js';

/**
 * Retourne les informations d'un menu.
 */
export async function getMenu(id) {

    const response = await apiFetch(`/api/menus/${id}`);

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer les informations du menu.'
        );

    }

    return await response.json();

}