import { getCurrentUser } from '../services/user.service.js';

/**
 * Charge les informations de l'utilisateur connecté.
 */
async function loadProfile() {

    if (!document.getElementById('profile-first-name')) {
        return;
    }

    try {

        const user = await getCurrentUser();

        document.getElementById('profile-first-name').textContent = user.firstName;
        document.getElementById('profile-last-name').textContent = user.lastName;
        document.getElementById('profile-email').textContent = user.email;
        document.getElementById('profile-phone').textContent = user.phone;
        document.getElementById('profile-street').textContent = user.street;
        document.getElementById('profile-postal-code').textContent = user.postalCode;
        document.getElementById('profile-city').textContent = user.city;

    } catch (error) {

        console.error(error);

    }

}

loadProfile();