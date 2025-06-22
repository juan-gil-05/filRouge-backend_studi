<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Food;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FoodFixtures extends Fixture implements DependentFixtureInterface
{
    public const FOOD_NB_TUPLES = 20;
    public const FOOD_REFERENCE = "food";

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= self::FOOD_NB_TUPLES; $i++) {
            $food = (new Food())
                ->setTitle($faker->word())
                ->setDescription($faker->sentence())
                ->setPrice($faker->randomFloat(2, 1, 500))
                ->addCategory($this->getReference(CategoryFixtures::CATEGORY_REFERENCE . random_int(1, 20), Category::class))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($food);
            $this->addReference(self::FOOD_REFERENCE . $i, $food);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}
