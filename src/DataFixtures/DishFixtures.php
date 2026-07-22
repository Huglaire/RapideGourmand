<?php

namespace App\DataFixtures;

use App\Entity\Dish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DishFixtures extends Fixture
{
    public const DISHES_REFERENCE = 'dishes';

    public function load(ObjectManager $manager): void
    {
        $dishes = [
            [
                'title' => 'Foie gras maison',
                'description' => 'Foie gras préparé par notre équipe avec accompagnement raffiné.',
                'price' => '12.00',
            ],
            [
                'title' => 'Filet de volaille sauce forestière',
                'description' => 'Filet de volaille accompagné de sa sauce aux champignons.',
                'price' => '18.00',
            ],
            [
                'title' => 'Tarte fine aux pommes',
                'description' => 'Dessert traditionnel aux pommes caramélisées.',
                'price' => '8.00',
            ],
            [
                'title' => 'Toast chèvre miel',
                'description' => 'Toast gourmand au fromage de chèvre et miel.',
                'price' => '6.00',
            ],
            [
                'title' => 'Risotto aux légumes',
                'description' => 'Risotto crémeux aux légumes de saison.',
                'price' => '14.00',
            ],
            [
                'title' => 'Mousse au chocolat',
                'description' => 'Mousse au chocolat réalisée maison.',
                'price' => '7.00',
            ],
        ];

        $references = [];

        foreach ($dishes as $data) {

            $dish = new Dish();

            $dish
                ->setTitle($data['title'])
                ->setDescription($data['description'])
                ->setPrice($data['price']);

            $manager->persist($dish);

            $references[] = $dish;
        }

        $manager->flush();

        foreach ($references as $index => $dish) {
            $this->addReference(
                self::DISHES_REFERENCE . '_' . $index,
                $dish
            );
        }
    }
}