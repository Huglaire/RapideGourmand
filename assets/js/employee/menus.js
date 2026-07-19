import {
    getMenus,
    deleteMenu,
    restoreMenu
} from '../api/employeeMenuApi.js';

let initialized = false;

document.addEventListener(
    'DOMContentLoaded',
    initMenusPage
);

document.addEventListener(
    'turbo:load',
    initMenusPage
);

/**
 * Initialise la page.
 */
async function initMenusPage() {

    const tableBody = document.getElementById(
        'menus-table-body'
    );

    if (!tableBody) {

        initialized = false;

        return;

    }

    if (initialized) {

        return;

    }

    initialized = true;

    await loadMenus();

}

/**
 * Charge les menus.
 */
async function loadMenus() {

    const tableBody = document.getElementById(
        'menus-table-body'
    );

    tableBody.replaceChildren();

    const menus = await getMenus();

    menus.forEach(menu => {

        tableBody.appendChild(
            createRow(menu)
        );

    });

}

/**
 * Construit une ligne du tableau.
 */
function createRow(menu) {

    const row = document.createElement(
        'tr'
    );

    row.appendChild(
        createCell(menu.title)
    );

    row.appendChild(
        createCell(`${menu.price} €`)
    );

    row.appendChild(
        createCell(menu.stock)
    );

    row.appendChild(
        createCell(
            menu.isAvailable
                ? 'Oui'
                : 'Non'
        )
    );

    row.appendChild(
        createActions(menu)
    );

    return row;

}

/**
 * Crée une cellule du tableau.
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
function createActions(menu) {

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
                `/employe/menus/${menu.id}/modifier`;

        }
    );

    td.appendChild(
        editButton
    );

    const toggleButton =
        document.createElement(
            'button'
        );

    toggleButton.className =
        menu.isAvailable
            ? 'btn btn-sm btn-outline-danger'
            : 'btn btn-sm btn-outline-success';

    toggleButton.textContent =
        menu.isAvailable
            ? 'Désactiver'
            : 'Restaurer';

    toggleButton.addEventListener(
        'click',
        async () => {

            if (menu.isAvailable) {

                await deleteMenu(
                    menu.id
                );

            } else {

                await restoreMenu(
                    menu.id
                );

            }

            initialized = false;

            await initMenusPage();

        }
    );

    td.appendChild(
        toggleButton
    );

    return td;

}