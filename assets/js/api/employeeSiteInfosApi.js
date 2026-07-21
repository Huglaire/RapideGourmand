import { apiFetch } from './client.js';

/**
 * Récupère toutes les informations du site.
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

/**
 * Met à jour une information du site.
 *
 * @param {number} id
 * @param {string} value
 */
export async function updateSiteInfo(
    id,
    value
) {

    const response = await apiFetch(
        `/api/site-infos/${id}`,
        {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                value
            })
        }
    );

    if (!response.ok) {

        throw new Error(
            'Impossible de mettre à jour cette information.'
        );

    }

    return await response.json();

}