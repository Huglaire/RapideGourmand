<?php

namespace App\DataFixtures;

use App\Entity\Dish;
use App\Entity\Picture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            DishFixtures::class,
        ];
    }


    public function load(ObjectManager $manager): void
    {
        $pictures = [
            [
                'title' => 'Foie gras maison',
                'alt' => 'Foie gras maison préparé par Rapide & Gourmand',
                'path' => 'images/dishes/foie-gras.jpg',
                'dish' => 0,
            ],
            [
                'title' => 'Filet de volaille sauce forestière',
                'alt' => 'Filet de volaille accompagné de sauce forestière',
                'path' => 'images/dishes/volaille.jpg',
                'dish' => 1,
            ],
            [
                'title' => 'Tarte fine aux pommes',
                'alt' => 'Tarte fine aux pommes caramélisées',
                'path' => 'images/dishes/tarte-pommes.jpg',
                'dish' => 2,
            ],
            [
                'title' => 'Toast chèvre miel',
                'alt' => 'Toast au chèvre et miel',
                'path' => 'images/dishes/toast-chevre.jpg',
                'dish' => 3,
            ],
            [
                'title' => 'Risotto aux légumes',
                'alt' => 'Risotto crémeux aux légumes de saison',
                'path' => 'images/dishes/risotto.jpg',
                'dish' => 4,
            ],
            [
                'title' => 'Mousse au chocolat',
                'alt' => 'Mousse au chocolat maison',
                'path' => 'images/dishes/mousse-chocolat.jpg',
                'dish' => 5,
            ],
        ];


        foreach ($pictures as $data) {

            $picture = new Picture();

            $picture
                ->setTitle($data['title'])
                ->setAlt($data['alt'])
                ->setPath($data['path']);


            $dish = $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_' . $data['dish'],
                Dish::class
            );


            $dish->addPicture($picture);

            $manager->persist($picture);
        }


        $manager->flush();
    }
}