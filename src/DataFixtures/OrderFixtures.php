<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Entity\Menu;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MenuFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(
            'user_fixture',
            User::class
        );

        $menu = $this->getReference(
            'menu_mariage',
            Menu::class
        );


        /*
         * Commande de test terminée
         */
        $order = new Order();

        $order
            ->setOrderDate(new \DateTimeImmutable('-10 days'))
            ->setDeliveryDate(new \DateTimeImmutable('+10 days'))
            ->setGuestNumber(60)
            ->setDeliveryStreet('3 rue du Traiteur')
            ->setDeliveryPostalCode('33000')
            ->setDeliveryCity('Bordeaux')
            ->setDeliveryFee(0.00)
            ->setTotalPrice(5100.00)
            ->setEquipmentBorrowed(false)
            ->setStatus(Order::STATUS_FINISHED)
            ->setUser($user);


        /*
         * Association commande / menu
         */
        $orderMenu = new OrderMenu();

        $orderMenu
            ->setQuantity(1)
            ->setMenu($menu)
            ->setCustomerOrder($order);


        $manager->persist($order);
        $manager->persist($orderMenu);

        $manager->flush();
    }
}