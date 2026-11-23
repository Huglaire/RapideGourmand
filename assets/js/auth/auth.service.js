/**
 * Supprime le jeton JWT et le panier local,
 * puis redirige vers la page d'accueil.
 */
export function logout() {

    localStorage.removeItem('jwt');
    localStorage.removeItem('cart');

    window.location.href = '/';

}