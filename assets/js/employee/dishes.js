import {
    getDishes,
    deleteDish
} from '../api/employeeDishApi.js';

let initialized = false;

document.addEventListener(
    'DOMContentLoaded',
    initDishesPage
);

document.addEventListener(
    'turbo:load',
    initDishesPage
);

/**
 * Initialise la page.
 */
async function initDishesPage() {

    const tableBody = document.getElementById(
        'dishes-table-body'
    );

    if (!tableBody) {

        initialized = false;

        return;

    }

    if (initialized) {

        return;

    }

    initialized = true;

    const createButton = document.getElementById(
        'create-dish-button'
    );

    createButton.addEventListener(
        'click',
        () => {

            window.location.href =
                '/employe/plats/creer';

        }
    );

    await loadDishes();

}

/**
 * Charge les plats.
 */
async function loadDishes() {

    const tableBody = document.getElementById(
        'dishes-table-body'
    );

    tableBody.replaceChildren();

    const dishes = await getDishes();

    dishes.forEach(dish => {

        tableBody.appendChild(
            createRow(dish)
        );

    });

}

/**
 * Construit une ligne du tableau.
 */
function createRow(dish) {

    const row = document.createElement(
        'tr'
    );

    row.appendChild(
        createCell(dish.title)
    );

    row.appendChild(
        createCell(`${dish.price} €`)
    );

    row.appendChild(
        createActions(dish)
    );

    return row;

}

/**
 * Crée une cellule.
 */
function createCell(content) {

    const td = document.createElement(
        'td'
    );

    td.textContent = content;

    return td;

}

/**
 * Crée les boutons d'action.
 */
function createActions(dish) {

    const td = document.createElement(
        'td'
    );

    td.className =
        'text-end';

    const editButton =
        document.createElement(
            'button'
        );

    editButton.className =
        'btn btn-sm btn-outline-primary me-2';

    editButton.textContent =
        'Modifier';

    editButton.addEventListener(
        'click',
        () => {

            window.location.href =
                `/employe/plats/${dish.id}/modifier`;

        }
    );

    td.appendChild(
        editButton
    );

    const deleteButton =
        document.createElement(
            'button'
        );

    deleteButton.className =
        'btn btn-sm btn-outline-danger';

    deleteButton.textContent =
        'Supprimer';

    deleteButton.addEventListener(
        'click',
        async () => {

            const confirmed = confirm(
                'Voulez-vous vraiment supprimer définitivement ce plat ?'
            );

            if (!confirmed) {

                return;

            }

            await deleteDish(
                dish.id
            );

            initialized = false;

            await initDishesPage();

        }
    );

    td.appendChild(
        deleteButton
    );

    return td;

}