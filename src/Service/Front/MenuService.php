<?php

namespace App\Service\Front;

use App\Repository\DietRepository;
use App\Repository\MenuRepository;
use App\Repository\ThemeRepository;

class MenuService
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
        private readonly ThemeRepository $themeRepository,
        private readonly DietRepository $dietRepository,
    ) {
    }

    /**
     * Prépare toutes les données nécessaires à la page des menus.
     */
    public function getMenuPageData(array $filters = []): array
    {
        return [
            'menus' => $this->menuRepository->findFilteredMenus($filters),
            'themes' => $this->themeRepository->findBy([], ['title' => 'ASC']),
            'diets' => $this->dietRepository->findBy([], ['title' => 'ASC']),
            'guestNumberRange' => $this->menuRepository->getGuestNumberRange(),
            'priceRange' => $this->menuRepository->getPriceRange(),
            'filters' => $filters,
        ];
    }
}