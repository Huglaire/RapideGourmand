import { getMenu } from '../services/menu.service.js';

/**
 * Contient les informations du menu affiché.
 */
let currentMenu = null;

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

    // N'affiche rien si le menu ne possède aucune image.
    if (menu.pictures.length === 0) {
        return;
    }

    // Affiche la première image comme image principale.
    const picture = menu.pictures[0];

    const image = document.getElementById('menu-main-picture');

    image.src = picture.path;
    image.alt = picture.alt;

    // Génère dynamiquement les miniatures de la galerie.
    const thumbnails = document.getElementById(
        'menu-gallery-thumbnails'
    );

    thumbnails.innerHTML = '';

    menu.pictures.forEach((picture) => {

        const column = document.createElement('div');

        column.classList.add('col-4');

        const thumbnail = document.createElement('img');

        thumbnail.src = picture.path;
        thumbnail.alt = picture.alt;

        thumbnail.classList.add(
            'menu-thumbnail',
            'img-fluid',
            'rounded'
        );

        // Met à jour l'image principale au clic sur une miniature.
        thumbnail.addEventListener('click', () => {

            image.src = picture.path;
            image.alt = picture.alt;

        });

        column.appendChild(thumbnail);

        thumbnails.appendChild(column);

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

    const container = document.getElementById('menu-dishes');

    container.innerHTML = '';

    menu.dishes.forEach((dish) => {

        const column = document.createElement('div');

        column.className = 'col-lg-4';

        column.innerHTML = `
            <div class="card h-100 shadow-sm">

                <div class="card-body">

                    <h3 class="h5 text-primary mb-3">
                        ${dish.title}
                    </h3>

                    ${
                        dish.pictures.length > 0
                            ? `
                            <img
                                src="${dish.pictures[0].path}"
                                alt="${dish.pictures[0].alt}"
                                class="dish-picture mb-3"
                            >
                            `
                            : ''
                    }

                    <p>
                        ${dish.description}
                    </p>

                    <strong>
                        Allergènes :
                    </strong>

                    <p>
                        ${
                            dish.allergens.length > 0
                                ? dish.allergens
                                    .map(allergen => allergen.title)
                                    .join('<br>')
                                : 'Aucun'
                        }
                    </p>

                </div>

            </div>
        `;

        container.appendChild(column);

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
async function loadMenu() {

    const container = document.querySelector('.menu-detail');

    if (!container) {
        return;
    }

    const menuId = container.dataset.menuId;

    try {

        currentMenu = await getMenu(menuId);

        displayMenu(currentMenu);

    } catch (error) {

        console.error(error);

    }

}

/**
 * Affiche le panneau de commande.
 */
function displayOrderPanel(menu) {

    const guestNumber =
        document.getElementById('guest-number');

    guestNumber.min = menu.minimumGuestNumber;

    guestNumber.value = menu.minimumGuestNumber;

    updateEstimatedPrice(menu);

    guestNumber.addEventListener('input', () => {

        updateEstimatedPrice(menu);

    });

}

/**
 * Met à jour le prix estimé.
 */
function updateEstimatedPrice(menu) {

    const guestNumber = Number(
        document.getElementById('guest-number').value
    );

    const estimatedPrice =
        guestNumber * Number(menu.price);

    document.getElementById(
        'menu-estimated-price'
    ).textContent =
        `${estimatedPrice.toFixed(2)} €`;

}

loadMenu();