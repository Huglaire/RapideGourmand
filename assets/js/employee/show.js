import {
    getOrder,
    updateOrderStatus,
    cancelOrder
} from '../api/employeeOrder.js';

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initOrderPage
);

document.addEventListener(
    'turbo:load',
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

    // Ajoute les actions disponibles sur la commande.
    container.append(
        createActionButtons(order)
    );
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

/**
 * Crée les boutons d'action disponibles selon le statut de la commande.
 */
function createActionButtons(order) {

    const wrapper =
        document.createElement('div');

    wrapper.classList.add(
        'mt-4',
        'd-flex',
        'gap-2'
    );

    // La commande est livrée : l'employé choisit la suite du workflow.
    if (order.status === 'Livré') {

        wrapper.append(
            createButton(
                'Attendre le retour du matériel',
                async () => {

                    await updateOrderStatus(
                        order.id,
                        'En attente du retour de matériel'
                    );

                    const updatedOrder =
                        await getOrder(order.id);

                    displayOrder(updatedOrder);

                }
            )
        );

        wrapper.append(
            createButton(
                'Terminer la commande',
                async () => {

                    await updateOrderStatus(
                        order.id,
                        'Terminée'
                    );

                    const updatedOrder =
                        await getOrder(order.id);

                    displayOrder(updatedOrder);

                }
            )
        );

        // Le matériel est revenu : la commande peut être clôturée.
    } else if (
        order.status ===
        'En attente du retour de matériel'
    ) {

        wrapper.append(
            createButton(
                'Terminer la commande',
                async () => {

                    await updateOrderStatus(
                        order.id,
                        'Terminée'
                    );

                    const updatedOrder =
                        await getOrder(order.id);

                    displayOrder(updatedOrder);

                }
            )
        );

    } else {

        const nextStatus =
            getNextStatus(order.status);

        // Affiche le bouton permettant d'avancer dans le workflow.
        if (nextStatus) {

            wrapper.append(
                createButton(
                    getButtonLabel(order.status),
                    async () => {

                        await updateOrderStatus(
                            order.id,
                            nextStatus
                        );

                        const updatedOrder =
                            await getOrder(order.id);

                        displayOrder(updatedOrder);

                    }
                )
            );

        }

    }

    // Une commande déjà terminée ou annulée ne peut plus être annulée.
    if (
        order.status !== 'Terminée' &&
        order.status !== 'Annulée'
    ) {

        wrapper.append(
            createButton(
                'Annuler la commande',
                async () => {

                    const reason =
                        prompt(
                            'Veuillez indiquer le motif de l\'annulation :'
                        );

                    if (
                        reason === null ||
                        reason.trim() === ''
                    ) {
                        return;
                    }

                    await cancelOrder(
                        order.id,
                        reason
                    );

                    const updatedOrder =
                        await getOrder(order.id);

                    displayOrder(updatedOrder);

                }
            )
        );

    }

    return wrapper;

}

/**
 * Retourne le statut suivant.
 */
function getNextStatus(status) {

    switch (status) {

        case 'En attente':
            return 'Accepté';

        case 'Accepté':
            return 'En préparation';

        case 'En préparation':
            return 'En cours de livraison';

        case 'En cours de livraison':
            return 'Livré';

        default:
            return null;

    }
}

/**
 * Crée un bouton.
 */
function createButton(
    label,
    callback
) {

    const button =
        document.createElement('button');

    button.classList.add(
        'btn',
        'btn-primary',
        'me-2'
    );

    button.textContent =
        label;

    button.addEventListener(
        'click',
        callback
    );

    return button;
}

/**
 * Retourne le texte du bouton.
 */
function getButtonLabel(status) {

    switch (status) {

        case 'En attente':
            return 'Accepter la commande';

        case 'Accepté':
            return 'Passer en préparation';

        case 'En préparation':
            return 'Passer en livraison';

        case 'En cours de livraison':
            return 'Marquer comme livrée';

        case 'En attente du retour de matériel':
            return 'Terminer la commande';

        default:
            return 'Mettre à jour le statut';

    }
}