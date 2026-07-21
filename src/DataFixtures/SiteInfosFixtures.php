<?php

namespace App\DataFixtures;

use App\Entity\SiteInfos;
use App\Repository\SiteInfosRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class SiteInfosFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(
        private readonly SiteInfosRepository $siteInfosRepository
    ) {
    }

    public static function getGroups(): array
    {
        return ['site_infos'];
    }

    public function load(ObjectManager $manager): void
    {
        $settings = [
            'opening_hours' => '',
            'terms_and_conditions' => '',
            'address' => '',
            'phone' => '',
            'contact_email' => '',
        ];

        foreach ($settings as $identifier => $value) {

            $existing = $this->siteInfosRepository->findOneBy([
                'identifier' => $identifier,
            ]);

            if ($existing) {
                continue;
            }

            $siteInfo = new SiteInfos();
            $siteInfo->setIdentifier($identifier);
            $siteInfo->setValue($value);

            $manager->persist($siteInfo);
        }

        $manager->flush();
    }
}