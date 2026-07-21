<?php

namespace App\Service;

use App\Repository\SiteInfosRepository;

class SiteInfosService
{
    public function __construct(
        private readonly SiteInfosRepository $siteInfosRepository
    ) {
    }

    /**
     * Retourne les informations du site sous forme d'un tableau associatif.
     */
    public function getAll(): array
    {
        $siteInfos = [];

        foreach ($this->siteInfosRepository->findAll() as $siteInfo) {

            $siteInfos[$siteInfo->getIdentifier()] = $siteInfo->getValue();

        }

        return $siteInfos;
    }
}