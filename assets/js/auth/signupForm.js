console.log('signup.js chargé');
function initializeSignupForm() {

    const form = document.getElementById('signup-form');

    if (!form || form.dataset.initialized === 'true') {
        return;
    }

    form.dataset.initialized = 'true';

    const errorBox = document.getElementById('signup-error');

    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        errorBox.classList.add('d-none');
        errorBox.textContent = '';

        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (password !== confirmPassword) {

            errorBox.textContent = 'Les mots de passe ne correspondent pas.';
            errorBox.classList.remove('d-none');

            return;
        }

        const payload = {
            firstName: document.getElementById('firstname').value.trim(),
            lastName: document.getElementById('lastname').value.trim(),
            email: document.getElementById('email').value.trim(),
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
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify(payload)

            });

            const data = await response.json();

            if (!response.ok) {

                errorBox.textContent = data.message ?? 'Une erreur est survenue.';
                errorBox.classList.remove('d-none');

                return;
            }

            alert('Compte créé avec succès !');

            window.location.href = '/signin';

        } catch (error) {

            console.error(error);

            errorBox.textContent = 'Impossible de contacter le serveur.';
            errorBox.classList.remove('d-none');

        }

    });

}

document.addEventListener('DOMContentLoaded', initializeSignupForm);
document.addEventListener('turbo:load', initializeSignupForm);