import {
    createMenu
} from '../api/employeeMenuApi.js';

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
 * Initialise le formulaire de création.
 */
async function init() {

    const page = document.querySelector(
        '#menu-create-page'
    );

    if (!page) {
        return;
    }


    const form = document.querySelector(
        '#menu-form'
    );

    if (!form) {
        return;
    }


    const [
        themes,
        diets,
        dishes
    ] = await Promise.all([
        getThemes(),
        getDiets(),
        getDishes()
    ]);


    displayThemes(themes);

    displayDiets(diets);

    displayDishes(dishes);


    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();

            await saveMenu();

        }
    );

}


/**
 * Affiche les thèmes.
 */
function displayThemes(themes) {

    const container = document.getElementById('themes');

    container.replaceChildren();


    themes.forEach(theme => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';


        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.name = 'themes';
        checkbox.value = theme.id;
        checkbox.id = `theme-${theme.id}`;


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
 * Affiche les régimes.
 */
function displayDiets(diets) {

    const container = document.getElementById('diets');

    container.replaceChildren();


    diets.forEach(diet => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';


        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.name = 'diets';
        checkbox.value = diet.id;
        checkbox.id = `diet-${diet.id}`;


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
 * Affiche les plats.
 */
function displayDishes(dishes) {

    const container = document.getElementById('dishes');

    container.replaceChildren();


    dishes.forEach(dish => {

        const wrapper = document.createElement('div');

        wrapper.className = 'form-check';


        const checkbox = document.createElement('input');

        checkbox.className = 'form-check-input';
        checkbox.type = 'checkbox';
        checkbox.name = 'dishes';
        checkbox.value = dish.id;
        checkbox.id = `dish-${dish.id}`;


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
 * Récupère les éléments sélectionnés.
 */
function getSelected(name) {

    return Array.from(
        document.querySelectorAll(
            `input[name="${name}"]:checked`
        )
    ).map(
        checkbox => Number(checkbox.value)
    );

}


/**
 * Enregistre le menu.
 */
async function saveMenu() {

    const data = {

        title:
            document.getElementById('title').value.trim(),

        description:
            document.getElementById('description').value.trim(),

        minimumGuestNumber:
            Number(
                document.getElementById('minimumGuestNumber').value
            ),

        price:
            Number(
                document.getElementById('price').value
            ),

        stock:
            Number(
                document.getElementById('stock').value
            ),

        conditions:
            document.getElementById('conditions').value.trim(),

        themes:
            getSelected('themes'),

        diets:
            getSelected('diets'),

        dishes:
            getSelected('dishes'),

        pictures: []

    };


    try {

        await createMenu(data);

        window.location.href =
            '/employe/menus';


    } catch (error) {

        console.error(error);

        alert(
            'Une erreur est survenue lors de la création du menu.'
        );

    }

}