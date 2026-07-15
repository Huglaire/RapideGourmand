import { logout } from './auth.service.js';

/**
 * Met à jour les actions de la barre de navigation
 * selon l'état de connexion.
 */
function updateNavbar() {

    const loginLink = document.getElementById('login-link');
    const profileLink = document.getElementById('profile-link');
    const logoutButton = document.getElementById('logout-button');

    if (!loginLink || !profileLink || !logoutButton) {
        return;
    }

    const isAuthenticated = localStorage.getItem('jwt') !== null;

    loginLink.classList.toggle('d-none', isAuthenticated);
    profileLink.classList.toggle('d-none', !isAuthenticated);
    logoutButton.classList.toggle('d-none', !isAuthenticated);

    logoutButton.addEventListener('click', logout);

}

updateNavbar();