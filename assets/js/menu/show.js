import { addToCart } from '../services/cart.service.js';
import { getMenu } from '../services/menu.service.js';

/**
 * Contient les informations du menu affiché.
 */
let currentMenu = null;

/**
 * État courant de la commande affichée.
 */
const orderState = {
    guestNumber: 0,
    unitPrice: 0,
    minimumGuestNumber: 0,
    subtotal: 0,
    discount: 0,
    total: 0
};

// Initialise la page lorsque le DOM est chargé.
document.addEventListener(
    'DOMContentLoaded',
    initMenuPage
);

/**
 * Initialise la page du menu.
 */
function initMenuPage() {
    // Quitte immédiatement le script si nous ne sommes pas
    // sur une page de détail d'un menu.
    const container =
        document.querySelector('.menu-detail');

    if (!container) {
        return;
    }

    loadMenu(container.dataset.menuId);
}

/**
 * Formate un montant au format français.
 */
function formatPrice(price) {
    return `${Number(price).toFixed(2).replace('.', ',')} €`;
}

/**
 * Affiche l'en-tête du menu.
 */
function displayHeader(menu) {
    document.getElementById('menu-title').textContent =
        menu.title;

    document.getElementById('menu-description').textContent =
        menu.description;
}

/**
 * Affiche la galerie du menu.
 */
function displayGallery(menu) {
    if (menu.pictures.length === 0) {
        return;
    }

    const mainPicture =
        document.getElementById('menu-main-picture');

    mainPicture.src = menu.pictures[0].path;
    mainPicture.alt = menu.pictures[0].alt;

    const container =
        document.getElementById('menu-gallery-thumbnails');

    const template =
        document.getElementById('gallery-thumbnail-template');

    container.innerHTML = '';

    menu.pictures.forEach((picture) => {

        const clone =
            template.content.cloneNode(true);

        const image =
            clone.querySelector('.menu-thumbnail');

        image.src = picture.path;
        image.alt = picture.alt;

        image.addEventListener('click', () => {

            mainPicture.src = picture.path;
            mainPicture.alt = picture.alt;

        });

        container.appendChild(clone);

    });
}

/**
 * Affiche les informations du menu.
 */
function displayInformation(menu) {
    document.getElementById('menu-theme').textContent =
        menu.themes.map(theme => theme.title).join(', ');

    document.getElementById('menu-diets').textContent =
        menu.diets.map(diet => diet.title).join(', ');

    document.getElementById('menu-min-guests').textContent =
        `${menu.minimumGuestNumber} personnes`;

    document.getElementById('menu-price').textContent =
        `${menu.price} €`;

    document.getElementById('menu-stock').textContent =
        menu.stock;

    document.getElementById('menu-conditions').textContent =
        menu.conditions;
}

/**
 * Affiche la composition du menu.
 */
function displayComposition(menu) {
    const container =
        document.getElementById('menu-dishes');

    const template =
        document.getElementById('dish-card-template');

    container.innerHTML = '';

    menu.dishes.forEach((dish) => {

        const clone =
            template.content.cloneNode(true);

        const image =
            clone.querySelector('.dish-picture');

        const title =
            clone.querySelector('.dish-title');

        const description =
            clone.querySelector('.dish-description');

        const allergens =
            clone.querySelector('.dish-allergens-list');

        if (dish.pictures.length > 0) {

            image.src = dish.pictures[0].path;
            image.alt = dish.pictures[0].alt;

        } else {

            image.remove();

        }

        title.textContent =
            dish.title;

        description.textContent =
            dish.description;

        allergens.textContent =
            dish.allergens.length > 0
                ? dish.allergens
                    .map(allergen => allergen.title)
                    .join(', ')
                : 'Aucun';

        container.appendChild(clone);

    });
}

/**
 * Affiche le menu.
 */
function displayMenu(menu) {
    displayHeader(menu);
    displayGallery(menu);
    displayInformation(menu);
    displayComposition(menu);
    displayOrderPanel(menu);
}

/**
 * Charge le menu.
 */
async function loadMenu(menuId) {
    try {

        currentMenu =
            await getMenu(menuId);

        displayMenu(currentMenu);

    } catch (error) {

        console.error(error);

    }
}

/**
 * Initialise le panneau de commande.
 */
function displayOrderPanel(menu) {
    orderState.unitPrice =
        Number(menu.price);

    orderState.minimumGuestNumber =
        menu.minimumGuestNumber;

    orderState.guestNumber =
        menu.minimumGuestNumber;

    document.getElementById('selected-menu-name').textContent =
        menu.title;

    document.getElementById('selected-menu-price').textContent =
        `${formatPrice(menu.price)} / personne`;

    const guestNumber =
        document.getElementById('guest-number');

    guestNumber.min =
        menu.minimumGuestNumber;

    guestNumber.value =
        menu.minimumGuestNumber;

    const minimumGuests =
        document.getElementById('minimum-guests');

    if (minimumGuests) {

        minimumGuests.textContent =
            `Minimum de commande : ${menu.minimumGuestNumber} personne${menu.minimumGuestNumber > 1 ? 's' : ''}`;

    }

    const discountThreshold =
        document.getElementById('discount-threshold');

    if (discountThreshold) {

        discountThreshold.textContent =
            `10 % de remise à partir de ${menu.minimumGuestNumber + 5} personnes.`;

    }

    updateEstimatedPrice();

    bindOrderPanelEvents();
}

/**
 * Associe les événements du panneau de commande.
 */
function bindOrderPanelEvents() {
    const guestNumber =
        document.getElementById('guest-number');

    guestNumber.oninput = () => {

        if (
            Number(guestNumber.value) <
            orderState.minimumGuestNumber
        ) {

            guestNumber.value =
                orderState.minimumGuestNumber;

        }

        orderState.guestNumber =
            Number(guestNumber.value);

        updateEstimatedPrice();

    };

    document
        .getElementById('order-button')
        .addEventListener('click', () => {

            const added = addToCart(
                currentMenu.id,
                orderState.guestNumber
            );

            if (!added) {
                return;
            }

            window.location.href = '/panier';

        });
}

/**
 * Recalcule le récapitulatif de la commande.
 */
function updateEstimatedPrice() {
    orderState.subtotal =
        orderState.unitPrice *
        orderState.guestNumber;

    // Applique la remise prévue à partir de
    // cinq personnes au-dessus du minimum.
    if (
        orderState.guestNumber >=
        orderState.minimumGuestNumber + 5
    ) {

        orderState.discount =
            orderState.subtotal * 0.10;

    } else {

        orderState.discount = 0;

    }

    orderState.total =
        orderState.subtotal -
        orderState.discount;

    document.getElementById('unit-price').textContent =
        formatPrice(orderState.unitPrice);

    document.getElementById('summary-guests').textContent =
        orderState.guestNumber;

    document.getElementById('subtotal').textContent =
        formatPrice(orderState.subtotal);

    const discountLabel =
        document.getElementById('discount-label');

    if (orderState.discount > 0) {

        discountLabel.textContent =
            'Remise (10 %)';

        document.getElementById('discount').textContent =
            `- ${formatPrice(orderState.discount)}`;

    } else {

        discountLabel.textContent =
            'Remise';

        document.getElementById('discount').textContent =
            formatPrice(0);

    }

    document.getElementById('total').textContent =
        formatPrice(orderState.total);
}