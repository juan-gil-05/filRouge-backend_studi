<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Menu;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public const MENU_NB_TUPLES = 20;
    public const MENU_REFERENCE = "menu";

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= self::MENU_NB_TUPLES; $i++) {
            $menu = (new Menu())
                ->setTitle($faker->word())
                ->setDescription($faker->sentence())
                ->setPrice($faker->randomFloat(2, 1, 500))
                ->setRestaurant($this->getReference(RestaurantFixtures::RESTAUTANT_REFERENCE . random_int(1, 20), Restaurant::class))
                ->addCategory($this->getReference(CategoryFixtures::CATEGORY_REFERENCE . random_int(1, 20), Category::class))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($menu);
            $this->addReference(self::MENU_REFERENCE . $i, $menu);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class, RestaurantFixtures::class];
    }
}
