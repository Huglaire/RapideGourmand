import {
    getMenu,
    updateMenu
} from '../api/menuEditApi.js';

import {
    getThemes
} from '../api/themeApi.js';

import {
    getDiets
} from '../api/dietApi.js';

import {
    getDishes
} from '../api/dishApi.js';

document.addEventListener(
    'DOMContentLoaded',
    init
);

document.addEventListener(
    'turbo:load',
    init
);

/**
 * Initialise la page d'édition.
 */
async function init() {

    const menuContainer = document.getElementById('menu-id');

    if (!menuContainer) {
        return;
    }

    const menuId = menuContainer.dataset.menuId;

    // Charge simultanément le menu, les thèmes, les régimes et les plats
    const [menu, themes, diets, dishes] = await Promise.all([
        getMenu(menuId),
        getThemes(),
        getDiets(),
        getDishes()
    ]);

    fillForm(menu);

    displayThemes(
        themes,
        menu.themes
    );

    displayDiets(
        diets,
        menu.diets
    );

    displayDishes(
        dishes,
        menu.dishes
    );

    const form = document.querySelector('#menu-form');

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
 * Remplit les champs du formulaire.
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
 * Affiche les thèmes disponibles.
 */
function displayThemes(
    themes,
    selectedThemes
) {

    const container = document.getElementById('themes');

    if (!container) {
        return;
    }

    container.replaceChildren();

    const selectedIds = selectedThemes.map(
        theme => theme.id
    );

    themes.forEach(theme => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';

        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.id = `theme-${theme.id}`;
        checkbox.name = 'themes';
        checkbox.value = theme.id;
        checkbox.checked = selectedIds.includes(theme.id);

        const label = document.createElement('label');

        label.className = 'form-check-label';
        label.htmlFor = checkbox.id;
        label.textContent = theme.title;

        wrapper.append(
            checkbox,
            label
        );

        container.append(wrapper);

    });

}

/**
 * Affiche les régimes alimentaires disponibles.
 */
function displayDiets(
    diets,
    selectedDiets
) {

    const container = document.getElementById('diets');

    if (!container) {
        return;
    }

    container.replaceChildren();

    const selectedIds = selectedDiets.map(
        diet => diet.id
    );

    diets.forEach(diet => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';

        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.id = `diet-${diet.id}`;
        checkbox.name = 'diets';
        checkbox.value = diet.id;
        checkbox.checked = selectedIds.includes(diet.id);

        const label = document.createElement('label');

        label.className = 'form-check-label';
        label.htmlFor = checkbox.id;
        label.textContent = diet.title;

        wrapper.append(
            checkbox,
            label
        );

        container.append(wrapper);

    });

}

/**
 * Affiche les plats disponibles.
 */
function displayDishes(
    dishes,
    selectedDishes
) {

    const container = document.getElementById('dishes');

    if (!container) {
        return;
    }

    container.replaceChildren();

    const selectedIds = selectedDishes.map(
        dish => dish.id
    );

    dishes.forEach(dish => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';

        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.id = `dish-${dish.id}`;
        checkbox.name = 'dishes';
        checkbox.value = dish.id;
        checkbox.checked = selectedIds.includes(dish.id);

        const label = document.createElement('label');

        label.className = 'form-check-label';
        label.htmlFor = checkbox.id;
        label.textContent = dish.title;

        wrapper.append(
            checkbox,
            label
        );

        container.append(wrapper);

    });

}

/**
 * Retourne les identifiants des thèmes sélectionnés.
 */
function getSelectedThemes() {

    return Array.from(
        document.querySelectorAll(
            'input[name="themes"]:checked'
        )
    ).map(
        checkbox => Number(checkbox.value)
    );

}

/**
 * Retourne les identifiants des régimes sélectionnés.
 */
function getSelectedDiets() {

    return Array.from(
        document.querySelectorAll(
            'input[name="diets"]:checked'
        )
    ).map(
        checkbox => Number(checkbox.value)
    );

}

/**
 * Retourne les identifiants des plats sélectionnés.
 */
function getSelectedDishes() {

    return Array.from(
        document.querySelectorAll(
            'input[name="dishes"]:checked'
        )
    ).map(
        checkbox => Number(checkbox.value)
    );

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
        conditions: document.getElementById('conditions').value,

        // Thèmes sélectionnés
        themes: getSelectedThemes(),

        // Régimes alimentaires sélectionnés
        diets: getSelectedDiets(),

        // Plats sélectionnés
        dishes: getSelectedDishes()

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