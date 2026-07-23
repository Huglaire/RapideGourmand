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


/**
 * Met à jour les informations de l'utilisateur connecté.
 */
export async function updateCurrentUser(user) {

    const response = await apiFetch('/api/me', {
        method: 'PATCH',
        body: JSON.stringify(user)
    });


    if (!response.ok) {

        throw new Error(
            'Impossible de mettre à jour le profil.'
        );

    }

    return await response.json();

}


/**
 * Désactive le compte de l'utilisateur connecté.
 */
export async function deleteCurrentUser() {

    const response = await apiFetch('/api/me', {
        method: 'DELETE'
    });


    if (!response.ok) {

        throw new Error(
            'Impossible de désactiver le profil.'
        );

    }

}