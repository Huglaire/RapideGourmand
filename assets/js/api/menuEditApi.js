import { apiFetch } from './client.js';

/**
 * Récupère un menu.
 */
export async function getMenu(id) {

    const response = await apiFetch(
        `/api/menus/${id}`
    );

    return response.json();

}

/**
 * Met à jour un menu.
 */
export async function updateMenu(id, data) {

    const response = await apiFetch(
        `/api/menus/${id}`,
        {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }
    );

    return response.json();

}