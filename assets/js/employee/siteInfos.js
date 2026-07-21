import {
    getSiteInfos,
    updateSiteInfo
} from '../api/employeeSiteInfosApi.js';

let initialized = false;

document.addEventListener(
    'DOMContentLoaded',
    initSiteInfosPage
);

document.addEventListener(
    'turbo:load',
    initSiteInfosPage
);

/**
 * Initialise la page.
 */
async function initSiteInfosPage() {

    const container = document.getElementById(
        'site-infos-container'
    );

    if (!container) {

        initialized = false;

        return;

    }

    if (initialized) {

        return;

    }

    initialized = true;

    registerEvents();

    await loadSiteInfos();

}

/**
 * Enregistre les événements.
 */
function registerEvents() {

    const saveButton = document.getElementById(
        'save-site-infos'
    );

    if (!saveButton) {

        return;

    }

    saveButton.removeEventListener(
        'click',
        saveSiteInfos
    );

    saveButton.addEventListener(
        'click',
        saveSiteInfos
    );

}

/**
 * Charge les informations.
 */
async function loadSiteInfos() {

    const container = document.getElementById(
        'site-infos-container'
    );

    container.replaceChildren();

    try {

        const siteInfos =
            await getSiteInfos();

        siteInfos.forEach(siteInfo => {

            container.appendChild(
                createField(siteInfo)
            );

        });

    } catch (error) {

        console.error(error);

        container.innerHTML = `
            <div class="alert alert-danger">
                Impossible de charger les informations du site.
            </div>
        `;

    }

}

/**
 * Sauvegarde toutes les informations.
 */
async function saveSiteInfos() {

    const button =
        document.getElementById(
            'save-site-infos'
        );

    button.disabled = true;

    try {

        const textareas =
            document.querySelectorAll(
                '.site-info-value'
            );

        for (const textarea of textareas) {

            await updateSiteInfo(
                textarea.dataset.id,
                textarea.value
            );

        }

        alert(
            'Les informations ont été enregistrées.'
        );

    } catch (error) {

        console.error(error);

        alert(
            'Une erreur est survenue lors de la sauvegarde.'
        );

    } finally {

        button.disabled = false;

    }

}

/**
 * Crée un champ d'édition.
 */
function createField(siteInfo) {

    const wrapper =
        document.createElement('div');

    wrapper.classList.add(
        'mb-4'
    );

    const label =
        document.createElement('label');

    label.classList.add(
        'form-label',
        'fw-bold'
    );

    label.textContent =
        formatLabel(
            siteInfo.identifier
        );

    const textarea =
        document.createElement('textarea');

    textarea.classList.add(
        'form-control',
        'site-info-value'
    );

    textarea.rows = 3;

    textarea.dataset.id =
        siteInfo.id;

    textarea.value =
        siteInfo.value ?? '';

    wrapper.append(
        label,
        textarea
    );

    return wrapper;

}

/**
 * Retourne le libellé à afficher.
 */
function formatLabel(identifier) {

    switch (identifier) {

        case 'address':
            return 'Adresse';

        case 'phone':
            return 'Téléphone';

        case 'contact_email':
            return 'Email de contact';

        case 'opening_hours':
            return 'Horaires';

        case 'terms_and_conditions':
            return 'Conditions générales de vente';

        default:
            return identifier;

    }

}