import { apiFetch } from '../api/client.js';

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initOrderPage
);

/**
 * Formate un prix.
 */
function formatPrice(price)
{
    return `${Number(price).toFixed(2).replace('.', ',')} €`;
}

/**
 * Initialise la page de commande.
 */
function initOrderPage()
{
    // Quitte immédiatement le script si nous ne sommes pas
    // sur la page de commande.
    const page =
        document.querySelector('.order-form');

    if (!page) {
        return;
    }

    loadOrder();
    bindForm();
}

/**
 * Charge les informations de la commande.
 */
function loadOrder()
{
    const order = JSON.parse(
        sessionStorage.getItem('currentOrder')
    );

    // Redirige l'utilisateur si aucune commande
    // n'est présente dans la session.
    if (!order) {

        window.location.href = '/menus';

        return;
    }

    const subtotal =
        order.guestNumber * order.unitPrice;

    let discount = 0;

    if (
        order.guestNumber >=
        order.minimumGuestNumber + 5
    ) {

        discount = subtotal * 0.10;

    }

    const total =
        subtotal - discount;

    // Alimente les champs cachés du formulaire.
    document.getElementById('menu-id').value =
        order.menuId;

    document.getElementById('guest-number-hidden').value =
        order.guestNumber;

    // Alimente le récapitulatif de la commande.
    document.getElementById('summary-menu').textContent =
        order.menuTitle;

    document.getElementById('summary-guests').textContent =
        order.guestNumber;

    document.getElementById('summary-unit-price').textContent =
        formatPrice(order.unitPrice);

    document.getElementById('summary-subtotal').textContent =
        formatPrice(subtotal);

    document.getElementById('summary-discount').textContent =
        discount > 0
            ? `- ${formatPrice(discount)}`
            : formatPrice(0);

    document.getElementById('summary-total').textContent =
        formatPrice(total);
}

/**
 * Associe les événements du formulaire.
 */
function bindForm()
{
    document
        .querySelector('.order-form form')
        .addEventListener(
            'submit',
            submitOrder
        );
}

/**
 * Envoie la commande à l'API.
 */
async function submitOrder(event)
{
    event.preventDefault();

    const order = JSON.parse(
        sessionStorage.getItem('currentOrder')
    );

    try {

        const response = await apiFetch(
            '/api/orders',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({

                    menuId: order.menuId,
                    guestNumber: order.guestNumber,

                    deliveryDate:
                        document.getElementById('delivery-date').value,

                    deliveryStreet:
                        document.getElementById('delivery-street').value,

                    deliveryPostalCode:
                        document.getElementById('delivery-postal-code').value,

                    deliveryCity:
                        document.getElementById('delivery-city').value

                })
            }
        );

        if (!response.ok) {

            throw new Error(
                'Impossible de créer la commande.'
            );

        }

        // Nettoie la session après la création de la commande.
        sessionStorage.removeItem(
            'currentOrder'
        );

        // Redirige l'utilisateur vers son historique.
        window.location.href =
            '/mes-commandes';

    } catch (error) {

        console.error(error);

        alert(error.message);

    }
}