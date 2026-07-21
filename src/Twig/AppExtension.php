<?php

namespace App\Twig;

use App\Service\SiteInfosService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly SiteInfosService $siteInfosService
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'site_infos' => $this->siteInfosService->getAll(),
        ];
    }
}