<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $themes = [
            'Mariage',
            'Anniversaire',
            'Entreprise',
            'Cocktail',
            'Baptême',
        ];

        foreach ($themes as $title) {

            $theme = new Theme();

            $theme->setTitle($title);

            $manager->persist($theme);

        }

        $manager->flush();
    }
}