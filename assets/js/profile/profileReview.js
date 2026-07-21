import { apiFetch } from '../api/client.js';

const reviewForm = document.getElementById('review-form');
const reviewMessage = document.getElementById('review-message');
const reviewAlreadySent = document.getElementById('review-already-sent');

/**
 * Vérifie si l'utilisateur a déjà publié un avis.
 */
async function checkReview() {

    if (!reviewForm) {
        return;
    }

    try {

        await apiFetch('/api/reviews/me');

        reviewForm.classList.add('d-none');
        reviewAlreadySent.classList.remove('d-none');

    } catch (error) {

        // 404 = aucun avis, on laisse le formulaire affiché
        if (error.status !== 404) {
            console.error(error);
        }

    }

}

/**
 * Gère l'envoi d'un avis.
 */
if (reviewForm) {

    checkReview();

    reviewForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        reviewMessage.textContent = '';
        reviewMessage.className = '';

        try {

            await apiFetch('/api/reviews', {
                method: 'POST',
                body: JSON.stringify({
                    rating: parseInt(document.getElementById('review-rating').value, 10),
                    comment: document.getElementById('review-comment').value.trim(),
                }),
            });

            reviewForm.classList.add('d-none');
            reviewAlreadySent.classList.remove('d-none');

            reviewMessage.className = 'alert alert-success';
            reviewMessage.textContent =
                'Merci ! Votre avis a bien été envoyé et sera publié après validation.';

        } catch (error) {

            reviewMessage.className = 'alert alert-danger';

            reviewMessage.textContent =
                error.message ?? 'Une erreur est survenue.';

        }

    });

}