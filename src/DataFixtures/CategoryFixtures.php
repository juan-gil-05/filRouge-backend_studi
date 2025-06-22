<?php

namespace App\DataFixtures;

use App\Entity\Category;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public const CATEGORY_NB_TUPLES = 20;
    public const CATEGORY_REFERENCE = "category";

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= self::CATEGORY_NB_TUPLES; $i++) {
            $category = (new Category())
                ->setTitle($faker->word())
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($category);
            $this->addReference(self::CATEGORY_REFERENCE . $i, $category);
        }
        $manager->flush();
    }

}
