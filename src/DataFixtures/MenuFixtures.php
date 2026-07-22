<?php

namespace App\DataFixtures;

use App\Entity\Dish;
use App\Entity\Menu;
use App\Repository\DietRepository;
use App\Repository\ThemeRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly ThemeRepository $themeRepository,
        private readonly DietRepository $dietRepository
    ) {
    }

    public function getDependencies(): array
    {
        return [
            ThemeFixtures::class,
            DietFixtures::class,
            DishFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        /*
         * Menu Mariage Prestige
         */
        $mariage = new Menu();

        $mariage
            ->setTitle('Menu Mariage Prestige')
            ->setDescription('Un menu complet et raffiné pour vos événements de mariage.')
            ->setPrice('85.00')
            ->setMinimumGuestNumber(50)
            ->setConditions('Commande minimum 50 personnes.')
            ->setStock(20)
            ->setIsAvailable(true);

        $themeMariage = $this->themeRepository->findOneBy([
            'title' => 'Mariage',
        ]);

        $dietClassique = $this->dietRepository->findOneBy([
            'title' => 'Classique',
        ]);

        if ($themeMariage) {
            $mariage->addTheme($themeMariage);
        }

        if ($dietClassique) {
            $mariage->addDiet($dietClassique);
        }

        $mariage->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_0',
                Dish::class
            )
        );

        $mariage->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_1',
                Dish::class
            )
        );

        $mariage->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_2',
                Dish::class
            )
        );

        $manager->persist($mariage);

        $this->addReference(
            'menu_mariage',
            $mariage
        );


        /*
         * Menu Cocktail Entreprise
         */
        $cocktail = new Menu();

        $cocktail
            ->setTitle('Cocktail Entreprise')
            ->setDescription('Une formule adaptée aux événements professionnels.')
            ->setPrice('45.00')
            ->setMinimumGuestNumber(20)
            ->setConditions('Commande minimum 20 personnes.')
            ->setStock(50)
            ->setIsAvailable(true);

        $themeEntreprise = $this->themeRepository->findOneBy([
            'title' => 'Entreprise',
        ]);

        $themeCocktail = $this->themeRepository->findOneBy([
            'title' => 'Cocktail',
        ]);

        $dietVegetarien = $this->dietRepository->findOneBy([
            'title' => 'Végétarien',
        ]);

        if ($themeEntreprise) {
            $cocktail->addTheme($themeEntreprise);
        }

        if ($themeCocktail) {
            $cocktail->addTheme($themeCocktail);
        }

        if ($dietVegetarien) {
            $cocktail->addDiet($dietVegetarien);
        }

        $cocktail->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_3',
                Dish::class
            )
        );

        $cocktail->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_4',
                Dish::class
            )
        );

        $cocktail->addDish(
            $this->getReference(
                DishFixtures::DISHES_REFERENCE . '_5',
                Dish::class
            )
        );

        $manager->persist($cocktail);

        $this->addReference(
            'menu_cocktail',
            $cocktail
        );


        $manager->flush();
    }
}