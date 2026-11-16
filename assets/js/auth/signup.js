/**
 * Initialisation du formulaire d'inscription.
 * Les contrôles sont effectués côté navigateur avant l'envoi à l'API.
 */
function initializeSignupForm() {

    const form = document.getElementById('signup-form');

    // Évite d'ajouter plusieurs fois les événements, notamment avec Turbo.
    if (!form || form.dataset.initialized) {
        return;
    }

    form.dataset.initialized = 'true';

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const error = document.getElementById('signup-error');

    // Expression régulière vérifiant le format général d'une adresse e-mail.
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    // Mot de passe : 8 caractères minimum, majuscule, minuscule,
    // chiffre et caractère spécial obligatoires.
    const passwordRegex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;

    form.addEventListener('submit', async (event) => {

        // Empêche l'envoi classique du formulaire.
        event.preventDefault();

        // Réinitialise le message d'erreur.
        error.classList.add('d-none');
        error.textContent = '';

        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Vérification du format de l'adresse e-mail.
        if (!emailRegex.test(email)) {

            error.textContent =
                'Veuillez saisir une adresse e-mail valide.';

            error.classList.remove('d-none');
            emailInput.focus();

            return;
        }

        // Vérification de la robustesse du mot de passe.
        if (!passwordRegex.test(password)) {

            error.textContent =
                'Le mot de passe doit contenir au moins 8 caractères, ' +
                'une majuscule, une minuscule, un chiffre et un caractère spécial.';

            error.classList.remove('d-none');
            passwordInput.focus();

            return;
        }

        // Vérification de la confirmation du mot de passe.
        if (password !== confirmPassword) {

            error.textContent =
                'Les mots de passe ne correspondent pas.';

            error.classList.remove('d-none');
            confirmPasswordInput.focus();

            return;
        }

        try {

            // Envoi des données à l'API Symfony.
            const response = await fetch('/api/register', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },

                body: JSON.stringify({

                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    email,
                    password,
                    phone: document.getElementById('phone').value,
                    street: document.getElementById('street').value,
                    postalCode: document.getElementById('postalCode').value,
                    city: document.getElementById('city').value

                })

            });

            const data = await response.json();

            // Gestion des erreurs renvoyées par le backend.
            if (!response.ok) {

                throw new Error(
                    data.message ?? 'Une erreur est survenue.'
                );

            }

            // Redirection après création réussie du compte.
            window.location.href = '/signin';

        } catch (exception) {

            // Affiche le message d'erreur retourné par l'API.
            error.textContent = exception.message;
            error.classList.remove('d-none');

        }

    });

}

// Initialisation lors du chargement classique de la page.
document.addEventListener('DOMContentLoaded', initializeSignupForm);

// Initialisation après une navigation Turbo.
document.addEventListener('turbo:load', initializeSignupForm);