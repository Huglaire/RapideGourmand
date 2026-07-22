<?php

namespace App\DataFixtures;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->getReference(
            'user_fixture',
            User::class
        );

        /*
         * Avis validé
         */
        $reviewApproved = new Review();

        $reviewApproved
            ->setRating(5)
            ->setComment('Une excellente expérience, les plats étaient délicieux.')
            ->setStatus('Validé')
            ->setUser($user);

        $manager->persist($reviewApproved);


        /*
         * Avis en attente
         */
        $reviewPending = new Review();

        $reviewPending
            ->setRating(4)
            ->setComment('Très bon service, je recommande.')
            ->setStatus('En attente')
            ->setUser($user);

        $manager->persist($reviewPending);

        $manager->flush();
    }
}