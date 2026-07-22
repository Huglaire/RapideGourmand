<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'reference' => 'admin_fixture',
                'email' => 'admin@mail.fr',
                'roles' => ['ROLE_ADMIN'],
                'firstName' => 'Admin',
                'lastName' => 'Rapide',
                'phone' => '0600000001',
                'street' => '1 rue du Traiteur',
                'postalCode' => '33000',
                'city' => 'Bordeaux',
            ],
            [
                'reference' => 'employee_fixture',
                'email' => 'employee@mail.fr',
                'roles' => ['ROLE_EMPLOYEE'],
                'firstName' => 'Employé',
                'lastName' => 'Rapide',
                'phone' => '0600000002',
                'street' => '2 rue du Traiteur',
                'postalCode' => '33000',
                'city' => 'Bordeaux',
            ],
            [
                'reference' => 'user_fixture',
                'email' => 'user@mail.fr',
                'roles' => ['ROLE_USER'],
                'firstName' => 'Client',
                'lastName' => 'Test',
                'phone' => '0600000003',
                'street' => '3 rue du Traiteur',
                'postalCode' => '33000',
                'city' => 'Bordeaux',
            ],
        ];

        foreach ($users as $data) {

            $user = $this->userRepository->findOneBy([
                'email' => $data['email'],
            ]);

            if (!$user) {
                $user = new User();

                $user->setEmail($data['email']);

                $user->setPassword(
                    $this->passwordHasher->hashPassword(
                        $user,
                        'password'
                    )
                );

                $manager->persist($user);
            }

            $user->setRoles($data['roles']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setPhone($data['phone']);
            $user->setStreet($data['street']);
            $user->setPostalCode($data['postalCode']);
            $user->setCity($data['city']);
            $user->setIsActive(true);

            $this->addReference(
                $data['reference'],
                $user
            );
        }

        $manager->flush();
    }
}