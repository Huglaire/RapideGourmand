import {
    getMenu,
    updateMenu
} from '../api/menuEditApi.js';

document.addEventListener(
    'DOMContentLoaded',
    init
);

document.addEventListener(
    'turbo:load',
    init
);

/**
 * Initialise la page.
 */
async function init() {

    const menuContainer = document.getElementById('menu-id');

    if (!menuContainer) {
        return;
    }

    const menuId = menuContainer.dataset.menuId;

    const menu = await getMenu(menuId);

    fillForm(menu);

    const form = document.querySelector('form');

    if (!form.dataset.listener) {

        form.dataset.listener = 'true';

        form.addEventListener(
            'submit',
            async (event) => {

                event.preventDefault();

                await saveMenu(menuId);

            }
        );

    }

}

/**
 * Remplit le formulaire.
 */
function fillForm(menu) {

    document.getElementById('title').value = menu.title;
    document.getElementById('description').value = menu.description ?? '';
    document.getElementById('minimumGuestNumber').value = menu.minimumGuestNumber;
    document.getElementById('price').value = menu.price;
    document.getElementById('stock').value = menu.stock;
    document.getElementById('conditions').value = menu.conditions ?? '';

}

/**
 * Enregistre le menu.
 */
async function saveMenu(menuId) {

    const data = {

        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        minimumGuestNumber: Number(
            document.getElementById('minimumGuestNumber').value
        ),
        price: Number(
            document.getElementById('price').value
        ),
        stock: Number(
            document.getElementById('stock').value
        ),
        conditions: document.getElementById('conditions').value

    };

    try {

        await updateMenu(
            menuId,
            data
        );

        alert('Menu enregistré avec succès.');

    } catch (error) {

        console.error(error);

        alert('Une erreur est survenue.');

    }

}