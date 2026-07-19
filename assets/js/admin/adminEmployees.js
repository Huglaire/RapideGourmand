import {
    getEmployees,
    createEmployee,
    disableEmployee,
    restoreEmployee
} from '../api/adminEmployeeApi.js';

let employeeModal;

document.addEventListener(
    'DOMContentLoaded',
    async () => {

        const modal =
            document.getElementById(
                'employeeModal'
            );

        if (!modal) {
            return;
        }

        employeeModal =
            new bootstrap.Modal(modal);

        const employeeForm =
            document.getElementById(
                'employee-form'
            );

        if (employeeForm) {

            employeeForm.addEventListener(
                'submit',
                handleEmployeeCreation
            );

        }

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

        showAlert(
            error.message,
            'danger'
        );

    }

}

/**
 * Affiche les employés.
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
        'col-md-6 col-xl-4';

    const card =
        document.createElement('div');

    card.className =
        'card shadow-sm h-100';

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

    const badge =
        document.createElement('span');

    badge.className =
        employee.isActive
            ? 'badge text-bg-success mb-3'
            : 'badge text-bg-danger mb-3';

    badge.textContent =
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

                    showAlert(
                        'Employé désactivé.',
                        'success'
                    );

                } else {

                    await restoreEmployee(
                        employee.id
                    );

                    showAlert(
                        'Employé réactivé.',
                        'success'
                    );

                }

                await loadEmployees();

            } catch (error) {

                showAlert(
                    error.message,
                    'danger'
                );

            }

        }
    );

    body.append(
        title,
        email,
        phone,
        badge,
        button
    );

    card.append(body);

    column.append(card);

    return column;

}

/**
 * Affiche une alerte Bootstrap.
 */
function showAlert(
    message,
    type
) {

    const alert =
        document.getElementById(
            'employee-alert'
        );

    if (!alert) {
        return;
    }

    alert.className =
        `alert alert-${type}`;

    alert.textContent =
        message;

    setTimeout(
        () => {

            alert.className =
                'alert d-none';

        },
        4000
    );

}

/**
 * Gère la création d'un employé.
 */
async function handleEmployeeCreation(event) {

    event.preventDefault();

    const form =
        event.currentTarget;

    const employee = {
        firstName: form.firstName.value.trim(),
        lastName: form.lastName.value.trim(),
        email: form.email.value.trim(),
        password: form.password.value,
        phone: form.phone.value.trim(),
        street: form.street.value.trim(),
        postalCode: form.postalCode.value.trim(),
        city: form.city.value.trim()
    };

    if (
        Object.values(employee)
            .some(value => value === '')
    ) {

        showAlert(
            'Tous les champs sont obligatoires.',
            'danger'
        );

        return;

    }

    const button =
        document.getElementById(
            'save-employee-button'
        );

    button.disabled = true;
    button.textContent = 'Création...';

    try {

        await createEmployee(employee);

        employeeModal.hide();

        form.reset();

        showAlert(
            'Employé créé avec succès.',
            'success'
        );

        await loadEmployees();

    } catch (error) {

        showAlert(
            error.message,
            'danger'
        );

    } finally {

        button.disabled = false;
        button.textContent =
            'Créer l\'employé';

    }

}