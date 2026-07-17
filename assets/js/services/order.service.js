import { apiFetch } from '../api/client.js';

/**
 * Retourne les commandes de l'utilisateur connecté.
 */
export async function getOrders()
{
    const response = await apiFetch('/api/orders');

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer vos commandes.'
        );

    }

    return await response.json();
}

/**
 * Crée une commande.
 */
export async function createOrder(orderData)
{
    const response = await apiFetch(
        '/api/orders',
        {
            method: 'POST',

            headers: {
                'Content-Type': 'application/json'
            },

            body: JSON.stringify(orderData)
        }
    );

    if (!response.ok) {

        let message =
            'Une erreur est survenue lors de la création de la commande.';

        try {

            const error =
                await response.json();

            if (error.message) {
                message = error.message;
            }

        } catch {

            // Aucune information complémentaire renvoyée par l'API.

        }

        throw new Error(message);
    }

    return await response.json();
}

/**
 * Annule une commande.
 */
export async function cancelOrder(
    orderId,
    cancelReason
)
{
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