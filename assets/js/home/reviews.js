import {
    apiFetch
} from '../api/client.js';


document.addEventListener(
    'DOMContentLoaded',
    loadHomeReviews
);


async function loadHomeReviews() {

    const container =
        document.getElementById(
            'home-reviews-container'
        );


    if (!container) {
        return;
    }


    const template =
        document.getElementById(
            'home-review-card-template'
        );


    try {

        const response =
            await apiFetch(
                '/api/reviews/home'
            );


        const reviews =
            await response.json();


        reviews.forEach(review => {

            const clone =
                template.content.cloneNode(true);


            clone.querySelector('.review-author')
                .textContent =
                review.user.firstName;


            clone.querySelector('.review-content')
                .textContent =
                review.comment ?? '';


            clone.querySelector('.review-stars')
                .textContent =
                generateStars(review.rating);


            container.appendChild(clone);

        });


    } catch (error) {

        console.error(error);

    }

}


function generateStars(rating) {

    return '★'.repeat(rating)
        + '☆'.repeat(5 - rating);

}