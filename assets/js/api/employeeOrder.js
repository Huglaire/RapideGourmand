import { apiFetch } from './client.js';

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

    if (response.status === 401) {

        localStorage.removeItem('jwt');

        window.location.href = '/signin';

        return [];

    }

    if (!response.ok) {
        throw new Error('Impossible de récupérer les commandes.');
    }

    return response.json();
}

export async function getOrder(orderId) {
    const response =
        await apiFetch(`/api/employee/orders/${orderId}`);

    if (response.status === 401) {

        localStorage.removeItem('jwt');

        window.location.href = '/signin';

        return null;
    }

    if (!response.ok) {
        throw new Error(
            'Impossible de récupérer la commande.'
        );
    }

    return response.json();
}

/**
 * Met à jour le statut d'une commande.
 */
export async function updateOrderStatus(
    orderId,
    status
) {

    const response = await apiFetch(
        `/api/employee/orders/${orderId}/status`,
        {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                status
            })
        }
    );

    if (response.status === 401) {

        localStorage.removeItem('jwt');
        window.location.href = '/signin';
        return null;

    }

    if (!response.ok) {
        throw new Error(
            'Impossible de mettre à jour le statut.'
        );
    }

    return response.json();
}

/**
 * Annule une commande.
 */
export async function cancelOrder(
    orderId,
    reason
) {

    const response =
        await apiFetch(
            `/api/employee/orders/${orderId}/cancel`,
            {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reason
                })
            }
        );

    if (response.status === 401) {

        localStorage.removeItem('jwt');

        window.location.href = '/signin';

        return null;

    }

    if (!response.ok) {

        throw new Error(
            'Impossible d\'annuler la commande.'
        );

    }

    return response.json();

}