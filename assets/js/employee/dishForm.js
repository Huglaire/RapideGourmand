import {
    getDish,
    createDish,
    updateDish
} from '../api/employeeDishApi.js';

import {
    getPictures,
    uploadPicture
} from '../api/pictureApi.js';

import {
    getAllergens
} from '../api/allergenApi.js';

const page = document.querySelector('#dish-form-page');

if (page) {

    const mode = page.dataset.mode;
    const dishId = page.dataset.id;

    const form = document.querySelector('#dish-form');

    const titleInput = document.querySelector('#dish-title');
    const descriptionInput = document.querySelector('#dish-description');
    const priceInput = document.querySelector('#dish-price');

    const picturesSelect = document.querySelector('#dish-pictures');
    const pictureUpload = document.querySelector('#dish-picture-upload');

    const previewContainer = document.querySelector(
        '#dish-picture-preview-container'
    );

    const previewImage = document.querySelector(
        '#dish-picture-preview'
    );

    const allergensSelect = document.querySelector('#dish-allergens');

    console.log('MODE :', mode);
    console.log('ID :', dishId);

    init();


    /**
     * Initialise la page.
     */
    async function init() {

        await loadPictures();
        await loadAllergens();

        if (pictureUpload) {

            pictureUpload.addEventListener(
                'change',
                handlePictureUpload
            );

        }

        if (mode === 'edit') {
            await loadDish();
        }

    }


    /**
     * Charge les images.
     */
    async function loadPictures() {

        const pictures = await getPictures();

        picturesSelect.innerHTML = '';

        pictures.forEach((picture) => {

            const option = document.createElement('option');

            option.value = picture.id;
            option.textContent = picture.title;

            picturesSelect.appendChild(option);

        });

    }


    /**
     * Charge les allergènes.
     */
    async function loadAllergens() {

        const allergens = await getAllergens();

        allergensSelect.innerHTML = '';

        allergens.forEach((allergen) => {

            const option = document.createElement('option');

            option.value = allergen.id;
            option.textContent = allergen.title;

            allergensSelect.appendChild(option);

        });

    }


    /**
     * Charge un plat existant.
     */
    async function loadDish() {

        try {

            const dish = await getDish(dishId);

            console.log('Plat récupéré :', dish);

            titleInput.value = dish.title;
            descriptionInput.value = dish.description ?? '';
            priceInput.value = dish.price;


            dish.pictures.forEach((picture) => {

                [...picturesSelect.options].forEach((option) => {

                    if (Number(option.value) === picture.id) {
                        option.selected = true;
                    }

                });

            });


            dish.allergens.forEach((allergen) => {

                [...allergensSelect.options].forEach((option) => {

                    if (Number(option.value) === allergen.id) {
                        option.selected = true;
                    }

                });

            });


        } catch (error) {

            console.error(
                'Erreur lors du chargement du plat :',
                error
            );

        }

    }


    /**
     * Upload d'une nouvelle image.
     */
    async function handlePictureUpload(event) {

        const file = event.target.files[0];

        if (!file) {
            return;
        }


        if (previewImage && previewContainer) {

            previewImage.src = URL.createObjectURL(file);

            previewContainer.classList.remove('d-none');

        }


        try {

            const picture = await uploadPicture(file);

            const option = document.createElement('option');

            option.value = picture.id;
            option.textContent = picture.title;
            option.selected = true;

            picturesSelect.appendChild(option);


        } catch (error) {

            console.error(error);

            alert(error.message);

        }

    }


    /**
     * Enregistre le plat.
     */
    form.addEventListener('submit', async (event) => {

        event.preventDefault();


        const data = {

            title: titleInput.value.trim(),

            description: descriptionInput.value.trim(),

            price: Number(priceInput.value),

            pictures: [
                ...picturesSelect.selectedOptions
            ].map(option => Number(option.value)),

            allergens: [
                ...allergensSelect.selectedOptions
            ].map(option => Number(option.value))

        };


        console.log(data);


        if (mode === 'create') {

            await createDish(data);

        } else {

            await updateDish(dishId, data);

        }


        window.location.href = '/employe/plats';

    });

}