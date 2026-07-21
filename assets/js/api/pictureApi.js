import { apiFetch } from './client.js';

/**
 * Récupère toutes les images.
 */
export async function getPictures() {

    const response = await apiFetch('/api/pictures');

    return await response.json();

}

/**
 * Upload une image.
 */
export async function uploadPicture(file) {

    const formData = new FormData();

    formData.append('file', file);

    const response = await apiFetch('/api/pictures', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {

        const error = await response.text();

        console.error(error);

        throw new Error(error);

    }

    return await response.json();

}