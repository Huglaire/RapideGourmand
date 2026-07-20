/**
 * Initialise les curseurs de filtres.
 */
function initMenuFilters() {

    console.log("initMenuFilters");

    document.querySelectorAll(".range-filter").forEach((filter) => {

        const range =
            filter.querySelector('input[type="range"]');

        const output =
            filter.querySelector(".range-output");

        if (
            !range ||
            !output
        ) {
            return;
        }

        // Évite d'initialiser plusieurs fois le même curseur.
        if (range.dataset.initialized === 'true') {
            return;
        }

        range.dataset.initialized = 'true';

        const suffix =
            output.textContent.includes("€")
                ? " €"
                : "";

        const updateOutput = () => {

            console.log("update", range.value);

            const value =
                Number(range.value);

            const min =
                Number(range.min);

            const max =
                Number(range.max);

            output.textContent =
                `${value}${suffix}`;

            const percent =
                (value - min) /
                (max - min);

            const rangeWidth =
                range.offsetWidth;

            const thumbWidth =
                18;

            const x =
                percent *
                (rangeWidth - thumbWidth) +
                thumbWidth / 2;

            output.style.left =
                `${x}px`;

        };

        updateOutput();

        console.log("binding", range);

        range.addEventListener(
            "input",
            updateOutput
        );

        window.addEventListener(
            "resize",
            updateOutput
        );

    });

}

document.addEventListener(
    "DOMContentLoaded",
    initMenuFilters
);

document.addEventListener(
    "turbo:load",
    initMenuFilters
);