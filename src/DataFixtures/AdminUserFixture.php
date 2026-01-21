<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Vérifier si l'admin existe déjà
        $existingAdmin = $manager->getRepository(User::class)->findOneBy(['email' => 'admin@gmail.com']);
        if ($existingAdmin) {
            return;
        }

        $admin = new User();
        $admin->setEmail('admin@gmail.com');
        $admin->setRoles(['ROLE_ADMIN']);

        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $manager->flush();
    }
}
