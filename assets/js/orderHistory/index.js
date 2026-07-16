import {
    getOrders,
    cancelOrder
} from '../services/order.service.js';

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

        const clone =
            template.content.cloneNode(true);

        clone.querySelector('.order-title').textContent =
            order.menuTitle;

        clone.querySelector('.order-date').textContent =
            formatDate(order.deliveryDate);

        clone.querySelector('.order-guests').textContent =
            order.guestNumber;

        clone.querySelector('.order-total').textContent =
            formatPrice(order.totalPrice);

        const badge =
            clone.querySelector('.order-status');

        badge.textContent = order.status;
        badge.classList.add(...getStatusClass(order.status).split(' '));

        const button =
            clone.querySelector('.cancel-order');

        button.addEventListener('click', async () => {

            try {

                await cancelOrder(order.id);

                loadOrders();

            } catch (error) {

                alert(error.message);

            }

        });

        container.appendChild(clone);

    });

}

/**
 * Charge les commandes.
 */
async function loadOrders() {

    try {

        const orders =
            await getOrders();

        displayOrders(orders);

    } catch (error) {

        console.error(error);

    }

}

loadOrders();