import { getMenu } from '../services/menu.service.js';

/**
 * Contient les informations du menu affiché.
 */
let currentMenu = null;

/**
 * Affiche les informations du menu.
 */
function displayMenu(menu) {

    document.getElementById('menu-title').textContent =
        menu.title;

    document.getElementById('menu-description').textContent =
        menu.description;

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

    document.getElementById('menu-estimated-price').textContent =
        `${menu.price} €`;

}

/**
 * Charge le menu.
 */
async function loadMenu() {

    const path = window.location.pathname;

    const menuId = path.split('/').pop();

    try {

        currentMenu = await getMenu(menuId);

        displayMenu(currentMenu);

    } catch (error) {

        console.error(error);

    }

}

loadMenu();