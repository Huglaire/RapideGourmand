import { apiFetch } from './client.js';

/**
 * Récupère la liste des menus.
 */
export async function getMenus() {

    const response = await apiFetch(
        '/api/menus'
    );

    const data = await response.json();

    if (!response.ok) {

        throw new Error(
            data.message ??
            'Impossible de récupérer les menus.'
        );

    }

    return data;

}

/**
 * Récupère le nombre de commandes par menu.
 */
export async function getOrdersPerMenu() {

    const response = await apiFetch(
        '/api/admin/statistics/orders-per-menu'
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
            'Impossible de récupérer les statistiques.'
        );

    }

    return data;

}

/**
 * Récupère le chiffre d'affaires.
 */
export async function getRevenue(
    menuId = '',
    start = '',
    end = ''
) {

    const params = new URLSearchParams();

    if (menuId) {

        params.append(
            'menuId',
            menuId
        );

    }

    if (start) {

        params.append(
            'start',
            start
        );

    }

    if (end) {

        params.append(
            'end',
            end
        );

    }

    const response = await apiFetch(
        `/api/admin/statistics/revenue?${params.toString()}`
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
            "Impossible de récupérer le chiffre d'affaires."
        );

    }

    return data;

}