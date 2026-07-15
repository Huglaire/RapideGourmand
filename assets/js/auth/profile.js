import { apiFetch } from '../api/client.js';

/**
 * Vérifie que l'utilisateur est authentifié.
 */
async function checkAuthentication() {

    try {

        const response = await apiFetch('/api/me');

        if (!response.ok) {

            throw new Error();

        }

        const user = await response.json();

        console.log('Utilisateur connecté :', user);

    } catch {

        console.log('Aucun utilisateur connecté.');

    }

}

checkAuthentication();