/**
 * Supprime le jeton JWT puis redirige vers la page d'accueil.
 */
export function logout() {

    localStorage.removeItem('jwt');

    window.location.href = '/';

}