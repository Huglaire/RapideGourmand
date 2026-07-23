/**
 * Envoie une requête vers l'API en ajoutant automatiquement
 * le jeton JWT lorsqu'il est disponible.
 */
export async function apiFetch(url, options = {}) {

    const token =
        localStorage.getItem('jwt');


    const headers = {
        ...(options.headers ?? {})
    };


    if (token) {

        headers.Authorization =
            `Bearer ${token}`;

    }


    const response =
        await fetch(
            url,
            {
                ...options,
                headers
            }
        );


    if (!response.ok) {

        let data = {};

        try {

            data =
                await response.json();

        } catch {

            // La réponse n'est pas un JSON exploitable.
        }


        const error =
            new Error(
                data.message ??
                'Une erreur est survenue.'
            );


        error.status =
            response.status;


        throw error;

    }


    return response;

}