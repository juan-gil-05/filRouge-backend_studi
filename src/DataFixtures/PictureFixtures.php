<?php

namespace App\DataFixtures;

use App\Entity\Picture;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public const PICTURE_NB_TUPLES = 20;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= self::PICTURE_NB_TUPLES; $i++) {
            $picture = (new Picture())
                ->setName($faker->word())
                ->setSlug("slug_$i")
                // Foreign key
                ->setRestaurant($this->getReference(RestaurantFixtures::RESTAUTANT_REFERENCE . random_int(1, 20), Restaurant::class))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($picture);
        }
        $manager->flush();
    }

    // The class Picture depends on class Restaurant
    public function getDependencies(): array
    {
        return [RestaurantFixtures::class];
    }
}
