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

    if (!response.ok) {

        throw new Error(
            'Impossible de récupérer les employés.'
        );

    }

    return response.json();

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

    if (!response.ok) {

        throw new Error(
            'Impossible de désactiver cet employé.'
        );

    }

    return response.json();

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

    if (!response.ok) {

        throw new Error(
            'Impossible de réactiver cet employé.'
        );

    }

    return response.json();

}