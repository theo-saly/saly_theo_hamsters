<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Hamster;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // --- ADMIN ---
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $admin->setGold(9999);

        $manager->persist($admin);


        // --- USERS ---
        for ($i = 1; $i <= 2; $i++) {

            $user = new User();
            $user->setEmail("user$i@example.com");
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, "password$i"));
            $user->setGold(500);

            $manager->persist($user);

            // Création automatique de 4 hamsters (2 mâles / 2 femelles)
            $this->createStarterHamsters($manager, $user);
        }

        $manager->flush();
    }

    private function createStarterHamsters(ObjectManager $manager, User $user): void
    {
        $genres = ['m', 'm', 'f', 'f'];

        foreach ($genres as $genre) {
            $faker = \Faker\Factory::create();
            $hamster = new Hamster();
            $hamster->setName($faker->name());
            $hamster->setAge(0);
            $hamster->setHunger(100);
            $hamster->setGenre($genre);
            $hamster->setActive(true);
            $hamster->setOwner($user);

            $manager->persist($hamster);
        }
    }
}
