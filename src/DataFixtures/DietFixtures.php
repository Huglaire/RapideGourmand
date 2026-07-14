<?php

namespace App\DataFixtures;

use App\Entity\Diet;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DietFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $diets = [
            'Classique',
            'Végétarien',
            'Vegan',
        ];

        foreach ($diets as $title) {

            $diet = new Diet();

            $diet->setTitle($title);

            $manager->persist($diet);

        }

        $manager->flush();
    }
}