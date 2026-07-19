import { apiFetch } from './client.js';

/**
 * Récupère tous les menus.
 */
export async function getMenus() {

    const response = await apiFetch(
        '/api/menus/employee'
    );
    return response.json();

}

/**
 * Désactive un menu.
 */
export async function deleteMenu(id) {

    return apiFetch(
        `/api/menus/${id}`,
        {
            method: 'DELETE'
        }
    );

}

/**
 * Réactive un menu.
 */
export async function restoreMenu(id) {

    return apiFetch(
        `/api/menus/${id}/restore`,
        {
            method: 'PATCH'
        }
    );

}