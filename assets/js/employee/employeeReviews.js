import { apiFetch } from '../api/client.js';

/**
 * Gère la validation et le refus des avis.
 */
document.addEventListener('click', async (event) => {

    const approveButton = event.target.closest('.review-approve');
    const rejectButton = event.target.closest('.review-reject');

    if (!approveButton && !rejectButton) {
        return;
    }

    const button = approveButton ?? rejectButton;
    const reviewId = button.dataset.reviewId;

    const action = approveButton ? 'approve' : 'reject';

    try {

        await apiFetch(`/api/admin/reviews/${reviewId}/${action}`, {
            method: 'PATCH',
        });

        window.location.reload();

    } catch (error) {

        console.error(error);
        alert("Une erreur est survenue.");

    }

});