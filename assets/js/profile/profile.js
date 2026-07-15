import { getCurrentUser } from '../services/user.service.js';

/**
 * Charge et affiche les informations de l'utilisateur connecté.
 */
async function loadProfile() {

    const container = document.getElementById('profile-content');

    if (!container) {
        return;
    }

    try {

        const user = await getCurrentUser();

        console.log(user);

        container.innerHTML = `
            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="h4 mb-4">
                        Mes informations
                    </h2>

                    <p><strong>Prénom :</strong> ${user.firstName}</p>

                    <p><strong>Nom :</strong> ${user.lastName}</p>

                    <p><strong>Email :</strong> ${user.email}</p>

                    <p><strong>Rôle :</strong> ${user.roles.join(', ')}</p>

                </div>

            </div>
        `;

    } catch (error) {

        container.innerHTML = `
            <div class="alert alert-danger">
                Impossible de charger votre profil.
            </div>
        `;

    }

}

loadProfile();