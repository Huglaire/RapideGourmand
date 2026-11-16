function initializeSignupForm() {

    const form = document.getElementById('signup-form');

    if (!form || form.dataset.initialized === 'true') {
        return;
    }

    form.dataset.initialized = 'true';

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirmPassword');

    const emailFeedback = document.getElementById('email-feedback');
    const confirmFeedback = document.getElementById('confirm-password-feedback');
    const errorBox = document.getElementById('signup-error');

    // Validation dynamique de l'adresse e-mail
    function validateEmail() {

        const email = emailInput.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        if (!email) {
            emailInput.classList.remove('is-valid', 'is-invalid');
            emailFeedback.textContent = '';
            return false;
        }

        const valid = regex.test(email);

        emailInput.classList.toggle('is-valid', valid);
        emailInput.classList.toggle('is-invalid', !valid);

        emailFeedback.textContent = valid
            ? 'Adresse e-mail au format correct.'
            : 'Veuillez saisir une adresse e-mail valide.';

        emailFeedback.className = valid
            ? 'form-text text-success'
            : 'form-text text-danger';

        return valid;
    }

    // Validation dynamique des criteres du mot de passe
    function validatePassword() {

        const password = passwordInput.value;

        const rules = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        const labels = {
            length: '8 caracteres minimum',
            lowercase: 'Une lettre minuscule',
            uppercase: 'Une lettre majuscule',
            number: 'Un chiffre',
            special: 'Un caractere special'
        };

        Object.entries(rules).forEach(([rule, valid]) => {

            const item = form.querySelector(
                `[data-rule="${rule}"]`
            );

            if (!item) {
                return;
            }

            item.classList.toggle('text-success', valid);

            item.classList.toggle(
                'text-danger',
                !valid && password.length > 0
            );

            item.classList.toggle(
                'text-muted',
                password.length === 0
            );

            item.textContent =
                `${valid ? '✓' : '○'} ${labels[rule]}`;

        });

        const valid = Object.values(rules).every(Boolean);

        passwordInput.classList.toggle(
            'is-valid',
            valid && password.length > 0
        );

        passwordInput.classList.toggle(
            'is-invalid',
            password.length > 0 && !valid
        );

        return valid;
    }

    // Verification de la confirmation du mot de passe
    function validateConfirmation() {

        const password = passwordInput.value;
        const confirmation = confirmInput.value;

        if (!confirmation) {

            confirmInput.classList.remove('is-valid', 'is-invalid');
            confirmFeedback.textContent = '';

            return false;
        }

        const valid = password === confirmation;

        confirmInput.classList.toggle('is-valid', valid);
        confirmInput.classList.toggle('is-invalid', !valid);

        confirmFeedback.textContent = valid
            ? 'Les mots de passe correspondent.'
            : 'Les mots de passe ne correspondent pas.';

        confirmFeedback.className = valid
            ? 'form-text text-success'
            : 'form-text text-danger';

        return valid;
    }

    // Ecoute des modifications pendant la saisie
    emailInput.addEventListener('input', validateEmail);

    passwordInput.addEventListener('input', () => {

        validatePassword();

        if (confirmInput.value) {
            validateConfirmation();
        }

    });

    confirmInput.addEventListener('input', validateConfirmation);

    // Soumission du formulaire
    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        errorBox.classList.add('d-none');
        errorBox.textContent = '';

        const emailValid = validateEmail();
        const passwordValid = validatePassword();
        const confirmationValid = validateConfirmation();

        if (!emailValid || !passwordValid || !confirmationValid) {
            return;
        }

        const password = passwordInput.value;

        const payload = {

            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            email: emailInput.value.trim(),
            password,
            phone: document.getElementById('phone').value.trim(),
            street: document.getElementById('street').value.trim(),
            postalCode: document.getElementById('postalCode').value.trim(),
            city: document.getElementById('city').value.trim()

        };

        try {

            const response = await fetch('/api/register', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },

                body: JSON.stringify(payload)

            });

            const data = await response.json();

            if (!response.ok) {

                errorBox.textContent =
                    data.message ?? 'Une erreur est survenue.';

                errorBox.classList.remove('d-none');

                return;
            }

            alert('Compte créé avec succès !');

            window.location.href = '/signin';

        } catch (error) {

            console.error(error);

            errorBox.textContent =
                'Impossible de contacter le serveur.';

            errorBox.classList.remove('d-none');

        }

    });

}

// Chargement classique
document.addEventListener('DOMContentLoaded', initializeSignupForm);

// Chargement apres une navigation Turbo
document.addEventListener('turbo:load', initializeSignupForm);