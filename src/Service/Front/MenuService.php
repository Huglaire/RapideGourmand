<?php

namespace App\Service\Front;

use App\Repository\MenuRepository;

class MenuService
{
    public function __construct(
        private readonly MenuRepository $menuRepository,
    ) {
    }

    /**
     * Retourne les menus disponibles pour le frontend.
     */
    public function getAvailableMenus(): array
    {
        return $this->menuRepository->findBy(
            ['isAvailable' => true],
            ['createdAt' => 'DESC']
        );
    }
}