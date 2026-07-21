import { logout } from './auth.service.js';
import { getCurrentUser } from '../services/user.service.js';

/**
 * Met à jour les actions de la barre de navigation
 * selon l'état de connexion.
 */
async function updateNavbar() {

    const loginLink = document.getElementById('login-link');
    const profileLink = document.getElementById('profile-link');
    const logoutButton = document.getElementById('logout-button');
    const employeeSpaceItem = document.getElementById('employee-space-item');

    if (!loginLink || !profileLink || !logoutButton || !employeeSpaceItem) {
        return;
    }

    const isAuthenticated = localStorage.getItem('jwt') !== null;

    loginLink.classList.toggle('d-none', isAuthenticated);
    profileLink.classList.toggle('d-none', !isAuthenticated);
    logoutButton.classList.toggle('d-none', !isAuthenticated);

    employeeSpaceItem.classList.add('d-none');

    if (isAuthenticated) {

        try {

            const currentUser = await getCurrentUser();
            const roles = currentUser.roles ?? [];

            if (
                roles.includes('ROLE_EMPLOYEE') ||
                roles.includes('ROLE_ADMIN')
            ) {
                employeeSpaceItem.classList.remove('d-none');
            }

        } catch (error) {

            console.error(error);

        }

    }

    logoutButton.removeEventListener('click', logout);
    logoutButton.addEventListener('click', logout);

}

updateNavbar();