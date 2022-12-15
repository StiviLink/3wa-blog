<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $comment = new Comment;
            $comment->setContent($faker->paragraph())
                ->setUser($this->getReference('admin' . rand(0, 4)))
                ->setPost($this->getReference('post' . rand(0, 9)))
            ;

            $manager->persist($comment);
        }

        for ($i = 0; $i < 5; $i++) {
            $comment = new Comment;
            $comment->setContent($faker->paragraph())
                ->setUser($this->getReference('user' . rand(0, 4)))
                ->setPost($this->getReference('post' . rand(0, 9)))
            ;

            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            PostFixtures::class
        ];
    }
}
