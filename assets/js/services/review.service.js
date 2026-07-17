import { apiFetch } from '../api/client.js';

// Récupère les avis validés.
export async function getReviews()
{
    return apiFetch('/api/reviews');
}

// Crée un nouvel avis.
export async function createReview(data)
{
    const response = await apiFetch('/api/reviews', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });

    // Interrompt l'exécution si la création de l'avis a échoué.
    if (!response.ok) {

        const error = await response.json();

        throw new Error(
            error.message
        );

    }

    return response.json();

}

// Met à jour un avis existant.
export async function updateReview(id, data)
{
    return apiFetch(`/api/reviews/${id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    });
}

// Supprime un avis.
export async function deleteReview(id)
{
    return apiFetch(`/api/reviews/${id}`, {
        method: 'DELETE'
    });
}