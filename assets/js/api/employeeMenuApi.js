import { apiFetch } from './client.js';

/**
 * Récupère tous les menus employés.
 */
export async function getMenus() {

    const response = await apiFetch(
        '/api/menus/employee'
    );

    return await response.json();

}


/**
 * Crée un menu.
 */
export async function createMenu(data) {

    const response = await apiFetch(
        '/api/menus',
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }
    );

    return await response.json();

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