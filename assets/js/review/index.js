import {
    getReviews,
    createReview
} from '../services/review.service.js';

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initReviewPage
);

/**
 * Initialise la page des avis.
 */
function initReviewPage() {
    // Quitte immédiatement le script si nous ne sommes pas
    // sur la page des avis.
    const container =
        document.getElementById('reviews-container');

    if (!container) {
        return;
    }

    loadReviews();
    bindForm();
}

/**
 * Associe le formulaire à son événement.
 */
function bindForm() {
    const form =
        document.getElementById('review-form');

    if (!form) {
        return;
    }

    form.addEventListener(
        'submit',
        submitReview
    );
}

/**
 * Affiche une alerte Bootstrap.
 */
function showAlert(type, message) {

    const alert =
        document.getElementById('review-alert');

    alert.className =
        `alert alert-${type}`;

    alert.textContent =
        message;

}

/**
 * Masque l'alerte.
 */
function hideAlert() {

    const alert =
        document.getElementById('review-alert');

    alert.className =
        'alert d-none';

    alert.textContent =
        '';

}

/**
 * Met à jour l'état du bouton d'envoi.
 */
function setSubmitLoading(isLoading) {

    const button =
        document.getElementById('review-submit');

    const spinner =
        document.getElementById('review-submit-spinner');

    const text =
        document.getElementById('review-submit-text');

    button.disabled =
        isLoading;

    spinner.classList.toggle(
        'd-none',
        !isLoading
    );

    text.textContent =
        isLoading
            ? 'Publication...'
            : 'Publier mon avis';

}

/**
 * Envoie un avis à l'API.
 */
async function submitReview(event) {

    event.preventDefault();

    hideAlert();

    // Active le mode chargement.
    setSubmitLoading(true);

    const rating =
        Number(
            document.getElementById('review-rating').value
        );

    const comment =
        document.getElementById('review-comment').value.trim();

    try {

        await createReview({
            rating,
            comment
        });

        showAlert(
            'success',
            'Votre avis a bien été enregistré. Il sera publié après validation.'
        );

        event.target.reset();

    } catch (error) {

        console.error(error);

        showAlert(
            'danger',
            error.message
        );

    } finally {

        // Réactive le bouton, que la requête ait réussi ou échoué.
        setSubmitLoading(false);

    }

}

/**
 * Formate une date.
 */
function formatDate(date) {
    return new Date(date).toLocaleDateString('fr-FR');
}

/**
 * Génère l'affichage des étoiles.
 */
function generateStars(rating) {
    return '★'.repeat(rating) + '☆'.repeat(5 - rating);
}

/**
 * Charge les avis depuis l'API.
 */
async function loadReviews() {
    try {

        const response =
            await getReviews();

        const reviews =
            await response.json();

        displayReviews(reviews);

    } catch (error) {

        console.error(error);

    }
}

/**
 * Affiche les avis.
 */
function displayReviews(reviews) {
    const container =
        document.getElementById('reviews-container');

    const template =
        document.getElementById('review-card-template');

    container.innerHTML = '';

    // Affiche un message si aucun avis n'est disponible.
    if (reviews.length === 0) {

        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun avis pour le moment.
                </div>
            </div>
        `;

        return;

    }

    reviews.forEach((review) => {

        // Génère une nouvelle carte.
        const clone =
            template.content.cloneNode(true);

        clone.querySelector('.review-rating').textContent =
            generateStars(review.rating);

        clone.querySelector('.review-author').textContent =
            review.user.firstName;

        clone.querySelector('.review-comment').textContent =
            review.comment;

        clone.querySelector('.review-date').textContent =
            formatDate(review.createdAt);

        container.appendChild(clone);

    });

}