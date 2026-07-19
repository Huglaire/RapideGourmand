import {
    getDish,
    createDish,
    updateDish
} from '../api/employeeDishApi.js';

import {
    getPictures
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
    const allergensSelect = document.querySelector('#dish-allergens');

    init();

    /**
     * Initialise la page.
     */
    async function init() {

        await loadPictures();
        await loadAllergens();

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
     * Charge un plat.
     */
    async function loadDish() {

        const dish = await getDish(dishId);

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

            pictures: [...picturesSelect.selectedOptions].map(option => Number(option.value)),

            allergens: [...allergensSelect.selectedOptions].map(option => Number(option.value))

        };

        if (mode === 'create') {

            await createDish(data);

        } else {

            await updateDish(dishId, data);

        }

        window.location.href = '/employe/plats';

    });

}