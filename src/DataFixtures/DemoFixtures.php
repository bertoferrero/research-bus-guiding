<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DemoFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $encoder)
    {
    }


    public function load(ObjectManager $manager): void
    {
        //User
        $user = new User();
        $user->setEmail('alberto.ferrero.lopez.eg@tut.jp');
        $user->setPassword($this->encoder->hashPassword($user, '123456789'));
        $user->setRoles(['ROLE_RIDER']);
        $manager->persist($user);

        //Driver
        $user = new User();
        $user->setEmail('driver@buscompany.com');
        $user->setPassword($this->encoder->hashPassword($user, '123456789'));
        $user->setRoles(['ROLE_DRIVER']);
        $manager->persist($user);

        $manager->flush();
    }
}
