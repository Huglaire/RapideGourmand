import { apiFetch } from '../api/client.js';

/**
 * Récupère les commandes employé.
 */
export async function getOrders(status = '', customer = '') {

    const params = new URLSearchParams();

    if (status) {
        params.append('status', status);
    }

    if (customer) {
        params.append('customer', customer);
    }

    const response = await apiFetch(
        `/api/employee/orders${params.toString() ? `?${params.toString()}` : ''}`
    );

    if (!response.ok) {
        throw new Error('Impossible de récupérer les commandes.');
    }

    return response.json();
}