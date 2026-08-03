import {
    getCart,
    removeFromCart,
    clearCart
} from '../services/cart.service.js';

import {
    getMenu
} from '../services/menu.service.js';

import {
    createOrder,
    calculateDeliveryFee
} from '../services/order.service.js';

// Initialise la page panier.
document.addEventListener(
    'DOMContentLoaded',
    initCartPage
);

/**
 * Initialise la page.
 */
async function initCartPage() {
    const cartContainer =
        document.getElementById('cart-content');

    const summaryContainer =
        document.getElementById('cart-summary-container');

    if (!cartContainer || !summaryContainer) {
        return;
    }

    cartContainer.replaceChildren();
    summaryContainer.replaceChildren();

    const cart =
        getCart();

    if (cart.length === 0) {

        const empty =
            document.createElement('p');

        empty.className =
            'text-center text-muted';

        empty.textContent =
            'Votre panier est vide.';

        cartContainer.appendChild(empty);

        return;
    }

    try {

        const items =
            await Promise.all(

                cart.map(async (item) => ({

                    menu:
                        await getMenu(item.menuId),

                    guestNumber:
                        item.guestNumber

                }))

            );

        renderCart(
            items,
            cartContainer,
            summaryContainer
        );

    } catch (error) {

        console.error(error);

    }
}

/**
 * Affiche le panier.
 */
function renderCart(
    items,
    cartContainer,
    summaryContainer
) {
    const itemTemplate =
        document.getElementById(
            'cart-item-template'
        );

    const summaryTemplate =
        document.getElementById(
            'cart-summary-template'
        );

    let subtotal = 0;

    items.forEach((item) => {

        const clone =
            itemTemplate.content.cloneNode(true);

        const total =
            Number(item.menu.price) *
            item.guestNumber;

        subtotal += total;

        clone.querySelector(
            '.cart-item__title'
        ).textContent =
            item.menu.title;

        clone.querySelector(
            '.cart-item__guests'
        ).textContent =
            `${item.guestNumber} personne${item.guestNumber > 1 ? 's' : ''}`;

        clone.querySelector(
            '.cart-item__price'
        ).textContent =
            `${Number(item.menu.price).toFixed(2)} € / personne`;

        clone.querySelector(
            '.cart-item__total'
        ).textContent =
            `${total.toFixed(2)} €`;

        clone.querySelector(
            '.cart-item__remove'
        ).addEventListener(
            'click',
            () => {

                removeFromCart(item.menu.id);

                initCartPage();

            }
        );

        cartContainer.appendChild(clone);

    });

    const summaryClone =
        summaryTemplate.content.cloneNode(true);

    const deliveryStreetInput =
        summaryClone.querySelector('#delivery-street');

    const deliveryPostalCodeInput =
        summaryClone.querySelector('#delivery-postal-code');

    const deliveryCityInput =
        summaryClone.querySelector('#delivery-city');

    const deliveryDateInput =
        summaryClone.querySelector('#delivery-date');

    summaryClone.querySelector(
        '.cart-summary__subtotal'
    ).textContent =
        `${subtotal.toFixed(2)} €`;

    summaryClone.querySelector(
        '.cart-summary__total'
    ).textContent =
        `${subtotal.toFixed(2)} €`;

    const deliveryElement =
        summaryClone.querySelector(
            '.cart-summary__delivery'
        );

    const totalElement =
        summaryClone.querySelector(
            '.cart-summary__total'
        );

    let debounceTimer;

    async function refreshDeliveryFee() {

        const deliveryStreet =
            deliveryStreetInput.value.trim();

        const deliveryPostalCode =
            deliveryPostalCodeInput.value.trim();

        const deliveryCity =
            deliveryCityInput.value.trim();

        if (
            !deliveryStreet ||
            !deliveryPostalCode ||
            !deliveryCity
        ) {

            deliveryElement.textContent =
                '5,00 €';

            totalElement.textContent =
                `${subtotal.toFixed(2)} €`;

            return;

        }

        try {

            const result =
                await calculateDeliveryFee(
                    deliveryStreet,
                    deliveryPostalCode,
                    deliveryCity
                );

            deliveryElement.textContent =
                `${Number(result.deliveryFee).toFixed(2)} €`;

            totalElement.textContent =
                `${(
                    subtotal +
                    Number(result.deliveryFee)
                ).toFixed(2)} €`;

        } catch (error) {

            console.error(error);

        }

    }

    summaryClone.querySelector(
        '.cart-summary__clear'
    ).addEventListener(
        'click',
        () => {

            clearCart();

            initCartPage();

        }
    );

    summaryClone.querySelector(
        '.cart-summary__checkout'
    ).addEventListener(
        'click',
        async () => {

            try {

                const item = items[0];

                if (!item) {

                    alert('Votre panier est vide.');

                    return;

                }

                const deliveryDate =
                    deliveryDateInput.value;

                const deliveryStreet =
                    deliveryStreetInput.value.trim();

                const deliveryPostalCode =
                    deliveryPostalCodeInput.value.trim();

                const deliveryCity =
                    deliveryCityInput.value.trim();

                if (
                    !deliveryDate ||
                    !deliveryStreet ||
                    !deliveryPostalCode ||
                    !deliveryCity
                ) {

                    alert(
                        'Veuillez compléter tous les champs de livraison.'
                    );

                    return;

                }

                await createOrder({

                    menuId:
                        item.menu.id,

                    guestNumber:
                        item.guestNumber,

                    deliveryDate,

                    deliveryStreet,

                    deliveryPostalCode,

                    deliveryCity

                });

                clearCart();

                window.location.href =
                    '/mes-commandes';

            } catch (error) {

                console.error(error);

                alert(error.message);

            }

        }
    );

    summaryContainer.appendChild(
        summaryClone
    );

    refreshDeliveryFee();

    [
        deliveryStreetInput,
        deliveryPostalCodeInput,
        deliveryCityInput
    ].forEach((input) => {

        input.addEventListener(
            'input',
            () => {

                clearTimeout(
                    debounceTimer
                );

                debounceTimer =
                    setTimeout(
                        refreshDeliveryFee,
                        500
                    );

            }
        );

    });
}