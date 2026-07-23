// Initialisation du formulaire de connexion
function initializeSigninForm() {

    const form = document.getElementById('signin-form');

    if (!form || form.dataset.initialized) {
        return;
    }

    form.dataset.initialized = 'true';


    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            console.log('Formulaire envoyé');


            const error =
                document.getElementById('signin-error');


            error.classList.add('d-none');

            error.textContent = '';



            const email =
                document.getElementById('email').value;


            const password =
                document.getElementById('password').value;



            try {


                const response =
                    await fetch(
                        '/api/login_check',
                        {

                            method: 'POST',

                            headers: {
                                'Content-Type': 'application/json'
                            },


                            body: JSON.stringify({
                                email,
                                password
                            })

                        }
                    );



                const data =
                    await response.json();



                if (!response.ok) {


                    /**
                     * Compte désactivé :
                     * on conserve l'email pour la page
                     * de réactivation.
                     */
                    if (
                        data.message ===
                        'Votre compte est désactivé.'
                    ) {


                        sessionStorage.setItem(
                            'restore_email',
                            email
                        );


                        window.location.href =
                            '/account/reactivate';


                        return;

                    }



                    throw new Error(
                        data.message ??
                        'Identifiants invalides.'
                    );

                }



                localStorage.setItem(
                    'jwt',
                    data.token
                );



                window.location.href =
                    '/';



            } catch (exception) {


                console.error(exception);


                error.textContent =
                    exception.message;


                error.classList.remove(
                    'd-none'
                );

            }

        }
    );

}



// Chargement classique
document.addEventListener(
    'DOMContentLoaded',
    initializeSigninForm
);



// Chargement après une navigation Turbo
document.addEventListener(
    'turbo:load',
    initializeSigninForm
);