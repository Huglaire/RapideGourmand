import { apiFetch } from '../api/client.js';

/**
 * Retourne les informations de l'utilisateur connecté.
 */
export async function getCurrentUser() {

    const response = await apiFetch('/api/me');

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer les informations utilisateur.'
        );

    }

    return await response.json();

}