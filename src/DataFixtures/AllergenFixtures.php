<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AllergenFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $allergens = [
            'Gluten',
            'Crustacés',
            'Œufs',
            'Poissons',
            'Arachides',
            'Soja',
            'Lait',
            'Fruits à coque',
            'Céleri',
            'Moutarde',
            'Graines de sésame',
            'Sulfites',
            'Lupin',
            'Mollusques',
        ];

        foreach ($allergens as $title) {

            $allergen = new Allergen();

            $allergen->setTitle($title);

            $manager->persist($allergen);

        }

        $manager->flush();
    }
}