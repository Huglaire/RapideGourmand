import { apiFetch } from './client.js';

/**
 * Récupère la liste des employés.
 */
export async function getEmployees() {

    const response = await apiFetch(
        '/api/admin/employees'
    );

    if (response.status === 401) {

        localStorage.removeItem('jwt');
        window.location.href = '/signin';

        return [];

    }

    const data = await response.json();

    if (!response.ok) {

        throw new Error(
            data.message ??
            'Impossible de récupérer les employés.'
        );

    }

    return data;

}

/**
 * Crée un nouvel employé.
 */
export async function createEmployee(employee) {

    const response = await apiFetch(
        '/api/admin/employees',
        {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(employee)
        }
    );

    const data = await response.json();

    if (!response.ok) {

        throw new Error(
            data.message ??
            'Impossible de créer le compte employé.'
        );

    }

    return data;

}

/**
 * Désactive un employé.
 */
export async function disableEmployee(id) {

    const response = await apiFetch(
        `/api/admin/employees/${id}/disable`,
        {
            method: 'PATCH'
        }
    );

    const data = await response.json();

    if (!response.ok) {

        throw new Error(
            data.message ??
            'Impossible de désactiver cet employé.'
        );

    }

    return data;

}

/**
 * Réactive un employé.
 */
export async function restoreEmployee(id) {

    const response = await apiFetch(
        `/api/admin/employees/${id}/restore`,
        {
            method: 'PATCH'
        }
    );

    const data = await response.json();

    if (!response.ok) {

        throw new Error(
            data.message ??
            'Impossible de réactiver cet employé.'
        );

    }

    return data;

}