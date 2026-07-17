import { getCart } from "../services/cart.service.js";

const cartContainer = document.getElementById("cart-content");

if (!cartContainer)
{
    // Nous ne sommes pas sur la page panier.
}
else
{
    init();
}

function init()
{
    const cart = getCart();

    console.log("Panier :", cart);

    if (cart.length === 0)
    {
        console.log("Panier vide");
    }
    else
    {
        console.log(`${cart.length} menu(x) dans le panier`);
    }
}