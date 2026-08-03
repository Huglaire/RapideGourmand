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

document.addEventListener(
    'DOMContentLoaded',
    initMenuPage
);


/**
 * Initialise la page du menu.
 */
function initMenuPage() {

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
 * Retourne le chemin correct d'une image.
 */
function getPicturePath(path) {

    if (!path) {
        return '';
    }

    return `/${path}`;

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

    const mainPicture =
        document.getElementById('menu-main-picture');


    const container =
        document.getElementById('menu-gallery-thumbnails');


    const template =
        document.getElementById('gallery-thumbnail-template');


    container.innerHTML = '';


    const gallery =
        menu.dishes.flatMap(dish => dish.pictures);



    if (gallery.length === 0) {

        mainPicture.src =
            'https://placehold.co/800x500';

        mainPicture.alt =
            menu.title;

        return;

    }



    mainPicture.src =
        getPicturePath(gallery[0].path);


    mainPicture.alt =
        gallery[0].alt;



    gallery.forEach((picture) => {


        const clone =
            template.content.cloneNode(true);


        const image =
            clone.querySelector('.menu-thumbnail');



        image.src =
            getPicturePath(picture.path);


        image.alt =
            picture.alt;



        image.addEventListener('click', () => {


            mainPicture.src =
                getPicturePath(picture.path);


            mainPicture.alt =
                picture.alt;


        });



        container.appendChild(clone);


    });


}


/**
 * Affiche les informations du menu.
 */
function displayInformation(menu) {


    document.getElementById('menu-theme').textContent =
        menu.themes
            .map(theme => theme.title)
            .join(', ');



    document.getElementById('menu-diets').textContent =
        menu.diets
            .map(diet => diet.title)
            .join(', ');



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


            image.src =
                getPicturePath(dish.pictures[0].path);



            image.alt =
                dish.pictures[0].alt;


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



            const added =
                addToCart(
                    currentMenu.id,
                    orderState.guestNumber
                );



            if (!added) {

                return;

            }



            window.location.href =
                '/panier';



        });


}


/**
 * Recalcule le récapitulatif de la commande.
 */
function updateEstimatedPrice() {


    orderState.subtotal =
        orderState.unitPrice *
        orderState.guestNumber;



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



    document.getElementById('discount').textContent =
        formatPrice(orderState.discount);



    document.getElementById('total').textContent =
        formatPrice(orderState.total);


}