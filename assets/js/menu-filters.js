document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".range-filter").forEach((filter) => {

        const range = filter.querySelector('input[type="range"]');
        const output = filter.querySelector(".range-output");

        if (!range || !output) {
            return;
        }

        const suffix = output.textContent.includes("€") ? " €" : "";

        const updateOutput = () => {

            const value = Number(range.value);
            const min = Number(range.min);
            const max = Number(range.max);

            output.textContent = `${value}${suffix}`;

            const percent = (value - min) / (max - min);

            const rangeWidth = range.offsetWidth;
            const thumbWidth = 18;

            const x = percent * (rangeWidth - thumbWidth) + thumbWidth / 2;

            output.style.left = `${x}px`;

        };

        updateOutput();

        range.addEventListener("input", updateOutput);

        window.addEventListener("resize", updateOutput);

    });

});