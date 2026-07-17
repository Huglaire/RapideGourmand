import { apiFetch } from '../api/client.js';

/**
 * Retourne les commandes de l'utilisateur connecté.
 */
export async function getOrders() {

    const response = await apiFetch('/api/orders');

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer vos commandes.'
        );

    }

    return await response.json();

}

/**
 * Annule une commande.
 */
export async function cancelOrder(
    orderId,
    cancelReason
) {

    const response = await apiFetch(

        `/api/orders/${orderId}/cancel`,

        {
            method: 'PATCH',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify({

                cancelReason

            })

        }

    );

    if (!response.ok) {

        throw new Error(
            'Impossible d\'annuler cette commande.'
        );

    }

    return await response.json();

}