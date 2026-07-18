import { getOrder } from '../api/employeeOrder.js';

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initOrderPage
);

/**
 * Initialise la page de détail d'une commande.
 */
async function initOrderPage() {

    const page =
        document.getElementById('employee-order-page');

    if (!page) {
        return;
    }

    const orderId =
        page.dataset.orderId;

    try {

        // Récupère les informations de la commande.
        const order =
            await getOrder(orderId);

        displayOrder(order);

    } catch (error) {

        console.error(error);

    }
}

/**
 * Affiche les informations de la commande.
 */
function displayOrder(order) {

    const container =
        document.getElementById('order-details');

    container.replaceChildren();

    container.append(
        createLine('Client', order.customer),
        createLine('Email', order.email),
        createLine('Menu', order.menuTitle),
        createLine('Date', order.deliveryDate),
        createLine('Invités', order.guestNumber),
        createLine(
            'Adresse',
            `${order.deliveryStreet}, ${order.deliveryPostalCode} ${order.deliveryCity}`
        ),
        createLine('Statut', order.status),
        createLine('Total', `${order.totalPrice} €`)
    );

    if (order.cancelReason) {

        container.append(
            createLine(
                'Motif',
                order.cancelReason
            )
        );

    }
}

/**
 * Crée une ligne d'information.
 */
function createLine(label, value) {

    const paragraph =
        document.createElement('p');

    const strong =
        document.createElement('strong');

    strong.textContent =
        `${label} : `;

    paragraph.append(
        strong,
        document.createTextNode(value)
    );

    return paragraph;
}