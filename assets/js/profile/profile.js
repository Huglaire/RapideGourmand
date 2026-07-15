import {
    getCurrentUser,
    updateCurrentUser
} from '../services/user.service.js';

/**
 * Contient les informations de l'utilisateur connecté.
 * Elles seront réutilisées lors de l'édition du profil.
 */
let currentUser = null;

/**
 * Affiche les informations de l'utilisateur.
 */
function displayProfile(user) {

    document.getElementById('profile-first-name').textContent = user.firstName;
    document.getElementById('profile-last-name').textContent = user.lastName;
    document.getElementById('profile-email').textContent = user.email;
    document.getElementById('profile-phone').textContent = user.phone;
    document.getElementById('profile-street').textContent = user.street;
    document.getElementById('profile-postal-code').textContent = user.postalCode;
    document.getElementById('profile-city').textContent = user.city;

}

/**
 * Remplit le formulaire d'édition avec les données actuelles.
 */
function fillProfileForm(user) {

    document.getElementById('firstName').value = user.firstName ?? '';
    document.getElementById('lastName').value = user.lastName ?? '';
    document.getElementById('email').value = user.email ?? '';
    document.getElementById('phone').value = user.phone ?? '';
    document.getElementById('street').value = user.street ?? '';
    document.getElementById('postalCode').value = user.postalCode ?? '';
    document.getElementById('city').value = user.city ?? '';

}

/**
 * Affiche le formulaire d'édition.
 */
function enableEditMode() {

    clearMessage();

    fillProfileForm(currentUser);

    document
        .getElementById('edit-profile-button')
        .classList.add('d-none');

    document
        .getElementById('profile-view')
        .classList.add('d-none');

    document
        .getElementById('profile-edit')
        .classList.remove('d-none');

}

/**
 * Revient à l'affichage du profil.
 */
function disableEditMode() {

    clearMessage();

    document
        .getElementById('profile-edit')
        .classList.add('d-none');

    document
        .getElementById('profile-view')
        .classList.remove('d-none');

    document
        .getElementById('edit-profile-button')
        .classList.remove('d-none');

}

/**
 * Affiche un message de succès.
 */
function showSuccessMessage(message) {

    const container = document.getElementById('profile-message');

    container.innerHTML = `
        <div class="alert alert-success" role="alert">
            ${message}
        </div>
    `;

}

/**
 * Affiche un message d'erreur.
 */
function showErrorMessage(message) {

    const container = document.getElementById('profile-message');

    container.innerHTML = `
        <div class="alert alert-danger" role="alert">
            ${message}
        </div>
    `;

}

/**
 * Supprime le message affiché.
 */
function clearMessage() {

    document.getElementById('profile-message').innerHTML = '';

}

/**
 * Enregistre les modifications du profil.
 */
async function saveProfile(event) {

    event.preventDefault();

    const user = {

        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        street: document.getElementById('street').value,
        postalCode: document.getElementById('postalCode').value,
        city: document.getElementById('city').value

    };

    try {

        currentUser = await updateCurrentUser(user);

        displayProfile(currentUser);

        disableEditMode();

        showSuccessMessage(
            'Vos informations ont été mises à jour avec succès.'
        );

    } catch (error) {

        showErrorMessage(error.message);

        console.error(error);

    }

}

/**
 * Associe les événements des boutons.
 */
function registerEvents() {

    document
        .getElementById('edit-profile-button')
        .addEventListener('click', enableEditMode);

    document
        .getElementById('cancel-profile-edit')
        .addEventListener('click', disableEditMode);

    document
        .getElementById('profile-form')
        .addEventListener('submit', saveProfile);

}

/**
 * Charge les informations de l'utilisateur connecté.
 */
async function loadProfile() {

    if (!document.getElementById('profile-first-name')) {
        return;
    }

    try {

        currentUser = await getCurrentUser();

        displayProfile(currentUser);

        registerEvents();

    } catch (error) {

        console.error(error);
        window.location.href = '/signin';

    }

}

loadProfile();