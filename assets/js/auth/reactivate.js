document.addEventListener(
    'DOMContentLoaded',
    initializeReactivateForm
);


document.addEventListener(
    'turbo:load',
    initializeReactivateForm
);


function initializeReactivateForm()
{

    const form =
        document.getElementById('reactivate-form');


    if (!form || form.dataset.initialized) {

        return;

    }


    form.dataset.initialized = 'true';


    const emailInput =
        document.getElementById('reactivate-email');


    const email =
        sessionStorage.getItem(
            'restore_email'
        );


    if (!email) {

        window.location.href = '/signin';

        return;

    }


    emailInput.value = email;



    form.addEventListener(
        'submit',
        async (event) => {

            event.preventDefault();


            const password =
                document.getElementById(
                    'reactivate-password'
                ).value;


            const message =
                document.getElementById(
                    'reactivate-message'
                );


            try {


                const response =
                    await fetch(
                        '/api/account/restore',
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

                    throw new Error(
                        data.message ??
                        'Impossible de réactiver le compte.'
                    );

                }



                sessionStorage.removeItem(
                    'restore_email'
                );


                message.innerHTML = `

                    <div class="alert alert-success">

                        Votre compte a été réactivé.
                        Vous pouvez maintenant vous connecter.

                    </div>

                `;


                setTimeout(
                    () => {

                        window.location.href =
                            '/signin';

                    },
                    2000
                );


            } catch(error) {


                message.innerHTML = `

                    <div class="alert alert-danger">

                        ${error.message}

                    </div>

                `;


            }


        }
    );

}