import {
    Chart,
    registerables
} from 'chart.js';

Chart.register(...registerables);

import {
    getMenus,
    getOrdersPerMenu,
    getRevenue
} from '../api/adminStatisticsApi.js';

let ordersChart = null;

document.addEventListener(
    'DOMContentLoaded',
    init
);

/**
 * Point d'entrée de la page.
 * Cette fonction est appelée automatiquement lorsque
 * le HTML est entièrement chargé.
 *
 * Elle lance ensuite les différents traitements nécessaires
 * pour construire l'interface.
 */
async function init() {

    try {

        await loadMenus();

        await loadStatistics();

        registerEvents();

    } catch (error) {

        console.error(error);

    }

}

/**
 * Charge la liste des menus depuis l'API afin d'alimenter
 * la liste déroulante des filtres.
 *
 * Les données proviennent de MySQL via l'API Symfony.
 * Elles permettront ensuite de filtrer le calcul du
 * chiffre d'affaires sur un menu précis.
 */
async function loadMenus() {

    const menus = await getMenus();

    const select = document.getElementById(
        'menu-select'
    );

    menus.forEach(menu => {

        const option = document.createElement(
            'option'
        );

        option.value = menu.id;

        option.textContent = menu.title;

        select.appendChild(option);

    });

}

/**
 * Récupère les statistiques calculées par l'API puis met à jour
 * les différents éléments de la page.
 *
 * Les données proviennent de MongoDB. L'agrégation est réalisée
 * côté Symfony afin de ne transmettre au navigateur que les
 * informations nécessaires à l'affichage.
 */
async function loadStatistics() {

    const statistics = await getOrdersPerMenu();

    updateSummaryCards(statistics);

    createOrdersChart(statistics);

}

/**
 * Met à jour les indicateurs affichés en haut de la page.
 */
function updateSummaryCards(statistics) {

    const totalOrdersElement = document.getElementById(
        'total-orders'
    );

    const bestMenuElement = document.getElementById(
        'best-menu'
    );

    const totalOrders = statistics.reduce(
        (total, menu) => total + menu.orderCount,
        0
    );

    totalOrdersElement.textContent = totalOrders;

    if (statistics.length === 0) {

        bestMenuElement.textContent = 'Aucune donnée';

        return;

    }

    const bestMenu = statistics.reduce(
        (best, current) =>
            current.orderCount > best.orderCount
                ? current
                : best
    );

    bestMenuElement.textContent = bestMenu.menuTitle;

}

/**
 * Construit le graphique comparant le nombre de commandes
 * de chaque menu.
 *
 * Chart.js attend deux tableaux distincts :
 * - labels : les libellés affichés sur l'axe horizontal ;
 * - data : les valeurs numériques à représenter.
 */
function createOrdersChart(statistics) {

    const canvas = document.getElementById(
        'orders-chart'
    );

    const context = canvas.getContext(
        '2d'
    );

    // Les statistiques provenant de MongoDB sont transformées
    // en tableaux simples afin d'être exploitables par Chart.js.
    const labels = statistics.map(
        menu => menu.menuTitle
    );

    const data = statistics.map(
        menu => menu.orderCount
    );

    // Si un graphique existe déjà, on le supprime avant
    // d'en créer un nouveau afin d'éviter les superpositions.
    if (ordersChart) {

        ordersChart.destroy();

    }

    // Création du graphique.
    // Chart.js dessine automatiquement dans le <canvas>
    // à partir des données fournies.
    ordersChart = new Chart(context, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Nombre de commandes',
                    data
                }
            ]
        },

        // Les options permettent de personnaliser l'affichage
        // sans modifier les données.
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

/**
 * Enregistre les événements de la page.
 */
function registerEvents() {

    const button = document.getElementById(
        'revenue-button'
    );

    button.addEventListener(
        'click',
        updateRevenue
    );

}

/**
 * Calcule le chiffre d'affaires selon les filtres sélectionnés.
 *
 * L'API retourne un tableau contenant les résultats correspondant
 * aux critères de recherche. Le chiffre d'affaires est ensuite
 * affiché dans la page.
 */
async function updateRevenue() {

    const menuId = document.getElementById(
        'menu-select'
    ).value;

    const start = document.getElementById(
        'start-date'
    ).value;

    const end = document.getElementById(
        'end-date'
    ).value;

    const revenues = await getRevenue(
        menuId,
        start,
        end
    );

    const result = document.getElementById(
        'revenue-result'
    );

    if (revenues.length === 0) {

        result.textContent =
            "Chiffre d'affaires : 0 €";

        return;

    }

    const totalRevenue = revenues.reduce(
        (total, menu) => total + menu.revenue,
        0
    );

    result.textContent =
        `Chiffre d'affaires : ${totalRevenue.toFixed(2)} €`;

}