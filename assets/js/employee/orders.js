import { getOrders } from '../api/employeeOrder.js';

document.addEventListener(
    'DOMContentLoaded',
    initOrdersPage
);

document.addEventListener(
    'turbo:load',
    initOrdersPage
);

/**
 * Initialise la page.
 */
function initOrdersPage() {
    const container =
        document.getElementById('orders-container');

    if (!container) {
        return;
    }

    registerEvents();

    loadOrders();
}

/**
 * Enregistre les événements de la page.
 */
function registerEvents() {
    const statusFilter =
        document.getElementById('status-filter');

    const customerFilter =
        document.getElementById('customer-filter');

    statusFilter.addEventListener(
        'change',
        loadOrders
    );

    customerFilter.addEventListener(
        'input',
        loadOrders
    );
}

/**
 * Charge les commandes.
 */
async function loadOrders() {
    try {

        const status =
            document.getElementById('status-filter').value;

        const customer =
            document.getElementById('customer-filter').value.trim();

        const orders =
            await getOrders(status, customer);

        renderOrders(orders);

    } catch (error) {

        console.error(error);

    }
}

/**
 * Affiche les commandes.
 */
function renderOrders(orders) {
    const container =
        document.getElementById('orders-container');

    container.replaceChildren();

    if (orders.length === 0) {

        container.append(
            createEmptyMessage()
        );

        return;
    }

    orders.forEach(order => {

        container.append(
            createOrderCard(order)
        );

    });
}

/**
 * Crée une carte commande.
 */
function createOrderCard(order) {
    const column =
        document.createElement('div');

    column.classList.add('col-lg-6');

    const card =
        document.createElement('div');

    card.classList.add(
        'card',
        'shadow-sm',
        'h-100'
    );

    const body =
        document.createElement('div');

    body.classList.add('card-body');

    body.append(
        createTitle(order),
        createInformation(order),
        createStatusBadge(order),
        createButtons(order)
    );

    card.append(body);

    column.append(card);

    return column;
}

/**
 * Crée le titre de la carte.
 */
function createTitle(order) {
    const title =
        document.createElement('h5');

    title.classList.add('card-title');

    title.textContent =
        order.customer;

    return title;
}

/**
 * Crée les informations de la commande.
 */
function createInformation(order) {
    const container =
        document.createElement('div');

    container.classList.add('mb-3');

    container.append(
        createInfoLine('Menu', order.menuTitle),
        createInfoLine('Date', order.deliveryDate),
        createInfoLine('Invités', order.guestNumber),
        createInfoLine('Ville', order.deliveryCity),
        createInfoLine(
            'Total',
            `${Number(order.totalPrice).toFixed(2)} €`
        )
    );

    return container;
}

/**
 * Crée une ligne d'information.
 */
function createInfoLine(label, value) {
    const paragraph =
        document.createElement('p');

    paragraph.classList.add('mb-2');

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

/**
 * Crée le badge de statut.
 */
function createStatusBadge(order) {
    const badge =
        document.createElement('span');

    badge.classList.add(
        'badge',
        'mb-3',
        getStatusClass(order.status)
    );

    badge.textContent =
        order.status;

    return badge;
}

/**
 * Crée les boutons d'action.
 */
function createButtons(order) {
    const container =
        document.createElement('div');

    container.classList.add(
        'd-flex',
        'gap-2',
        'mt-3'
    );

    container.append(
        createViewButton(order)
    );

    return container;
}

/**
 * Crée le bouton Voir.
 */
function createViewButton(order) {
    const button =
        createButton(
            'Voir',
            'btn-primary-custom'
        );

    button.dataset.orderId =
        order.id;

    button.addEventListener(
        'click',
        () => {
            window.location.href =
                `/employe/commandes/${order.id}`;
        }
    );

    return button;
}

/**
 * Crée un bouton d'action.
 */
function createButton(label, style) {
    const button =
        document.createElement('button');

    button.type = 'button';

    button.classList.add(
        'btn',
        style
    );

    button.textContent =
        label;

    return button;
}

/**
 * Affiche un message lorsqu'aucune commande n'est disponible.
 */
function createEmptyMessage() {
    const column =
        document.createElement('div');

    column.classList.add('col-12');

    const alert =
        document.createElement('div');

    alert.classList.add(
        'alert',
        'alert-info'
    );

    alert.textContent =
        'Aucune commande à afficher.';

    column.append(alert);

    return column;
}

function getStatusClass(status) {
    switch (status) {

        case 'En attente':
            return 'text-bg-warning';

        case 'Validée':
            return 'text-bg-success';

        case 'Annulée':
            return 'text-bg-danger';

        default:
            return 'text-bg-secondary';

    }
}