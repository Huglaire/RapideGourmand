import { apiFetch } from '../api/client.js';

/**
 * Attend que toute la page soit chargée avant d'exécuter le script.
 */
document.addEventListener('DOMContentLoaded', async () => {

    // Conteneur qui accueillera les champs de saisie.
    const container = document.getElementById('site-infos-container');

    try {

        // Appelle l'API en ajoutant automatiquement le JWT.
        const response = await apiFetch('/api/site-infos/employee');

        if (!response.ok) {
            throw new Error('Erreur lors du chargement des informations du site.');
        }

        // Convertit la réponse JSON en objet JavaScript.
        const siteInfos = await response.json();

        // Supprime le message "Chargement...".
        container.innerHTML = '';

        // Génère un bloc de saisie pour chaque information.
        siteInfos.forEach(siteInfo => {

            container.insertAdjacentHTML('beforeend', `
                <div class="mb-4">

                    <label class="form-label fw-bold">
                        ${formatLabel(siteInfo.identifier)}
                    </label>

                    <textarea
                        class="form-control site-info-value"
                        rows="3"
                        data-id="${siteInfo.id}"
                    >${siteInfo.value ?? ''}</textarea>

                </div>
            `);

        });

    } catch (error) {

        // Affiche un message d'erreur si l'appel API échoue.
        container.innerHTML = `
            <div class="alert alert-danger">
                Impossible de charger les informations du site.
            </div>
        `;

        console.error(error);

    }

});

/**
 * Associe les identifiants enregistrés en base
 * à un libellé compréhensible pour l'utilisateur.
 *
 * @param {string} identifier
 * @returns {string}
 */
function formatLabel(identifier) {

    const labels = {
        address: 'Adresse',
        phone: 'Téléphone',
        contact_email: 'Email de contact',
        opening_hours: 'Horaires',
        terms_and_conditions: 'Conditions générales de vente'
    };

    return labels[identifier] ?? identifier;

}