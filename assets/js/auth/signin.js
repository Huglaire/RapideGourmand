console.log('signin.js chargé');

// Gestion de la soumission du formulaire de connexion
const form = document.getElementById('signin-form');

if (form) {

    form.addEventListener('submit', async (event) => {

        event.preventDefault();

        console.log('Formulaire envoyé');

        const error = document.getElementById('signin-error');

        error.classList.add('d-none');
        error.textContent = '';

        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        try {

            // Envoi des identifiants à l'API
            const response = await fetch('/api/login_check', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    email,
                    password
                })

            });

            const data = await response.json();

            // Affichage d'une erreur si l'authentification échoue
            if (!response.ok) {

                throw new Error(
                    data.message ?? 'Identifiants invalides.'
                );

            }

            // Conservation du jeton JWT pour les futurs appels API
            localStorage.setItem(
                'jwt',
                data.token
            );

            // Redirection après une connexion réussie
            window.location.href = '/';

        } catch (exception) {

            error.textContent = exception.message;
            error.classList.remove('d-none');

        }

    });

}