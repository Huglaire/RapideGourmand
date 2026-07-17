const STORAGE_KEY = "cart";

/**
 * Retourne le contenu du panier.
 */
export function getCart()
{
    const cart = localStorage.getItem(STORAGE_KEY);

    return cart ? JSON.parse(cart) : [];
}

/**
 * Sauvegarde le panier.
 */
function saveCart(cart)
{
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
}

/**
 * Ajoute un menu au panier.
 */
export function addToCart(menuId, guestNumber)
{
    const cart = getCart();

    const existingMenu = cart.find(item => item.menuId === menuId);

    if (existingMenu)
    {
        existingMenu.guestNumber = guestNumber;
    }
    else
    {
        cart.push({
            menuId,
            guestNumber
        });
    }

    saveCart(cart);
}

/**
 * Supprime un menu.
 */
export function removeFromCart(menuId)
{
    const cart = getCart().filter(item => item.menuId !== menuId);

    saveCart(cart);
}

/**
 * Vide le panier.
 */
export function clearCart()
{
    localStorage.removeItem(STORAGE_KEY);
}

/**
 * Vérifie si un menu est présent.
 */
export function isInCart(menuId)
{
    return getCart().some(item => item.menuId === menuId);
}