const STORAGE_KEY = 'cart';

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
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify(cart)
    );
}

/**
 * Ajoute un menu au panier.
 */
export function addToCart(menuId, guestNumber)
{
    const cart = getCart();

    // Le panier est vide.
    if (cart.length === 0) {

        saveCart([
            {
                menuId,
                guestNumber
            }
        ]);

        return true;
    }

    const currentItem = cart[0];

    // Le même menu est déjà présent.
    if (currentItem.menuId === menuId) {

        currentItem.guestNumber = guestNumber;

        saveCart(cart);

        return true;
    }

    // Un autre menu est déjà présent.
    const confirmed = window.confirm(
        'Vous avez déjà sélectionné un menu. Souhaitez-vous le remplacer ?'
    );

    if (!confirmed) {
        return false;
    }

    saveCart([
        {
            menuId,
            guestNumber
        }
    ]);

    return true;
}

/**
 * Supprime le menu du panier.
 */
export function removeFromCart(menuId)
{
    const cart = getCart().filter(
        item => item.menuId !== menuId
    );

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
    return getCart().some(
        item => item.menuId === menuId
    );
}