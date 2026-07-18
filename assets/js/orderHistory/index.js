import {
    getOrders,
    cancelOrder
} from '../services/order.service.js';

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initOrderHistoryPage
);

/**
 * Initialise la page d'historique des commandes.
 */
function initOrderHistoryPage() {
    // Quitte immédiatement le script si on n'est pas
    // sur la page "Mes commandes".
        const container =
            document.getElementById('orders-container');

        const template =
            document.getElementById('order-card-template');

        if (!container || !template) {
            return;
        }

        loadOrders();
}


    /**
     * Formate un prix.
     */
    function formatPrice(price) {
        return `${Number(price).toFixed(2).replace('.', ',')} €`;
    }

    /**
     * Formate une date.
     */
    function formatDate(date) {
        return new Date(date).toLocaleDateString('fr-FR');
    }

    /**
     * Retourne la classe Bootstrap correspondant au statut.
     */
    function getStatusClass(status) {
        switch (status) {

            case 'En attente':
                return 'bg-warning text-dark';

            case 'Validée':
                return 'bg-success';

            case 'Annulée':
                return 'bg-danger';

            default:
                return 'bg-secondary';

        }
    }

    /**
     * Affiche les commandes.
     */
    function displayOrders(orders) {
        const container =
            document.getElementById('orders-container');

        const template =
            document.getElementById('order-card-template');

        container.innerHTML = '';

        // Affiche un message si aucune commande n'a été passée.
        if (orders.length === 0) {

            container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info">
                    Vous n'avez encore passé aucune commande.
                </div>
            </div>
        `;

            return;
        }

        orders.forEach((order) => {

            // Génère une nouvelle carte à partir du template HTML.
            const orderCard =
                template.content.cloneNode(true);

            orderCard.querySelector('.order-title').textContent =
                order.menuTitle;

            orderCard.querySelector('.order-date').textContent =
                formatDate(order.deliveryDate);

            orderCard.querySelector('.order-created-at').textContent =
                formatDate(order.createdAt);

            orderCard.querySelector('.order-address').textContent =
                `${order.deliveryStreet}, ${order.deliveryPostalCode} ${order.deliveryCity}`;

            orderCard.querySelector('.order-guests').textContent =
                order.guestNumber;

            orderCard.querySelector('.order-total').textContent =
                formatPrice(order.totalPrice);

            // Adapte la couleur du badge selon le statut.
            const badge =
                orderCard.querySelector('.order-status');

            badge.textContent = order.status;
            badge.classList.add(
                ...getStatusClass(order.status).split(' ')
            );

            const button =
                orderCard.querySelector('.cancel-order');

            // Seules les commandes en attente peuvent être annulées.
            if (order.status !== 'En attente') {

                button.remove();

            } else {

                button.addEventListener('click', async () => {

                    const cancelReason = prompt(
                        'Veuillez indiquer le motif de votre annulation :'
                    );

                    if (
                        cancelReason === null ||
                        cancelReason.trim() === ''
                    ) {
                        return;
                    }

                    try {

                        await cancelOrder(
                            order.id,
                            cancelReason
                        );

                        // Recharge la liste afin d'afficher le nouveau statut.
                        await loadOrders();

                    } catch (error) {

                        alert(error.message);

                    }

                });

            }

            container.appendChild(orderCard);

        });
    }

    /**
     * Charge les commandes de l'utilisateur connecté.
     */
    async function loadOrders() {
        try {

            // Récupère les commandes depuis l'API.
            const orders =
                await getOrders();

            displayOrders(orders);

        } catch (error) {

            console.error(error);

        }
    }