import { apiFetch } from './client.js';

/**
 * Récupère les informations du site.
 *
 * @returns {Promise<Array>}
 */
export async function getSiteInfos() {

    const response = await apiFetch(
        '/api/site-infos/employee'
    );

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer les informations du site.'
        );

    }

    return await response.json();

}