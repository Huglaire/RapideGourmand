/**
 * Envoie une requête vers l'API en ajoutant automatiquement
 * le jeton JWT lorsqu'il est disponible.
 */
export async function apiFetch(url, options = {}) {

    const token = localStorage.getItem('jwt');

    const headers = {
        ...(options.headers ?? {})
    };

    if (token) {

        headers.Authorization = `Bearer ${token}`;

    }

    const response = await fetch(url, {
        ...options,
        headers
    });

    return response;

}