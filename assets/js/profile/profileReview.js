import { apiFetch } from '../api/client.js';

const reviewForm = document.getElementById('review-form');
const reviewMessage = document.getElementById('review-message');
const reviewAlreadySent = document.getElementById('review-already-sent');

/**
 * Gère l'envoi d'un avis.
 */
if (reviewForm) {

    reviewForm.addEventListener('submit', async (event) => {

        event.preventDefault();

        reviewMessage.textContent = '';
        reviewMessage.className = '';
        reviewAlreadySent.classList.add('d-none');

        try {

            await apiFetch('/api/reviews', {
                method: 'POST',
                body: JSON.stringify({
                    rating: parseInt(document.getElementById('review-rating').value, 10),
                    comment: document.getElementById('review-comment').value.trim(),
                }),
            });

            reviewForm.classList.add('d-none');

            reviewMessage.className = 'alert alert-success';
            reviewMessage.textContent =
                'Merci ! Votre avis a bien été envoyé et sera publié après validation.';

        } catch (error) {

            if (error.status === 409) {

                reviewAlreadySent.classList.remove('d-none');

            } else {

                reviewMessage.className = 'alert alert-danger';
                reviewMessage.textContent =
                    error.message ?? 'Une erreur est survenue.';
            }
        }
    });
}