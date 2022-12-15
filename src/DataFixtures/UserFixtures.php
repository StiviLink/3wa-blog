<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $user = new User;
        $user->setusername('Stivi')
            ->setEmail('stivi@gmail.com')
            ->setPassword($this->hasher->hashPassword($user, 'password'))
            ->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $user = new User;
        $user->setusername('admin')
            ->setEmail('admin@blog.com')
            ->setPassword($this->hasher->hashPassword($user, 'password'))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $manager->persist($user);

        $faker = Faker\Factory::create('fr_FR');

        for($i = 0; $i < 5; $i++) {

            $user = new User;
            $user->setusername($faker->userName())
                ->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER']);

            $manager->persist($user);
            $this->addReference('user' . $i, $user);
        }

        for($i = 0; $i < 5; $i++) {

            $user = new User;
            $user->setusername($faker->userName())
                ->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

            $this->addReference('admin' . $i, $user);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
