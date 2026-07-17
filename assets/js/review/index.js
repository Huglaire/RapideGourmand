import { getReviews } from '../services/review.service.js';

// Initialise la page au chargement du DOM
document.addEventListener(
    'DOMContentLoaded',
    initReviewPage
);

async function initReviewPage()
{
    try {

        // Charge les avis depuis l'API
        const reviews = await getReviews();

        console.log(reviews);

    } catch (error) {

        // Affiche les erreurs dans la console
        console.error(error);

    }
}