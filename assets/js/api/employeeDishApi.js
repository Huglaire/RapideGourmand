import { apiFetch } from './client.js';

/**
 * Récupère la liste des plats.
 */
export async function getDishes() {

    const response = await apiFetch('/api/dishes');

    return await response.json();

}

/**
 * Récupère un plat.
 */
export async function getDish(id) {

    const response = await apiFetch(`/api/dishes/${id}`);

    return await response.json();

}

/**
 * Crée un plat.
 */
export async function createDish(data) {

    const response = await apiFetch('/api/dishes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();

}

/**
 * Modifie un plat.
 */
export async function updateDish(id, data) {

    const response = await apiFetch(`/api/dishes/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    return await response.json();

}

/**
 * Supprime un plat.
 */
export async function deleteDish(id) {

    const response = await apiFetch(`/api/dishes/${id}`, {
        method: 'DELETE'
    });

    return await response.json();

}

/**
 * Restaure un plat.
 */
export async function restoreDish(id) {

    const response = await apiFetch(`/api/dishes/${id}/restore`, {
        method: 'PATCH'
    });

    return await response.json();

}