import {
    getEmployees,
    disableEmployee,
    restoreEmployee
} from '../api/adminEmployeeApi.js';

document.addEventListener(
    'DOMContentLoaded',
    async () => {

        await loadEmployees();

    }
);

/**
 * Charge les employés.
 */
async function loadEmployees() {

    const container =
        document.getElementById(
            'employees-container'
        );

    try {

        const employees =
            await getEmployees();

        displayEmployees(
            container,
            employees
        );

    } catch (error) {

        console.error(error);

        container.textContent =
            'Impossible de charger les employés.';

    }

}

/**
 * Affiche les cartes.
 */
function displayEmployees(
    container,
    employees
) {

    container.replaceChildren();

    if (employees.length === 0) {

        const message =
            document.createElement('p');

        message.textContent =
            'Aucun employé trouvé.';

        container.append(message);

        return;

    }

    employees.forEach(employee => {

        container.append(
            createEmployeeCard(employee)
        );

    });

}

/**
 * Construit une carte employé.
 */
function createEmployeeCard(employee) {

    const column =
        document.createElement('div');

    column.className =
        'col-md-6 col-lg-4';

    const card =
        document.createElement('div');

    card.className =
        'card h-100 shadow-sm';

    const body =
        document.createElement('div');

    body.className =
        'card-body d-flex flex-column';

    const title =
        document.createElement('h2');

    title.className =
        'h5';

    title.textContent =
        `${employee.firstName} ${employee.lastName}`;

    const email =
        document.createElement('p');

    email.className =
        'mb-2';

    email.textContent =
        employee.email;

    const phone =
        document.createElement('p');

    phone.className =
        'mb-3';

    phone.textContent =
        employee.phone;

    const status =
        document.createElement('span');

    status.className =
        employee.isActive
            ? 'badge text-bg-success mb-3'
            : 'badge text-bg-danger mb-3';

    status.textContent =
        employee.isActive
            ? 'Actif'
            : 'Désactivé';

    const button =
        document.createElement('button');

    button.className =
        employee.isActive
            ? 'btn btn-outline-danger mt-auto'
            : 'btn btn-outline-success mt-auto';

    button.textContent =
        employee.isActive
            ? 'Désactiver'
            : 'Réactiver';

    button.addEventListener(
        'click',
        async () => {

            try {

                if (employee.isActive) {

                    await disableEmployee(
                        employee.id
                    );

                } else {

                    await restoreEmployee(
                        employee.id
                    );

                }

                await loadEmployees();

            } catch (error) {

                console.error(error);

                alert(
                    'Une erreur est survenue.'
                );

            }

        }
    );

    body.append(
        title,
        email,
        phone,
        status,
        button
    );

    card.append(body);

    column.append(card);

    return column;

}