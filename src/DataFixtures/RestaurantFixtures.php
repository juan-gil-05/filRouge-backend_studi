<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RestaurantFixtures extends Fixture implements DependentFixtureInterface
{

    public const RESTAURANT_NB_TUPLES = 20;
    public const RESTAUTANT_REFERENCE = "restaurant";


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= SELF::RESTAURANT_NB_TUPLES; $i++) {
            $restaurant = (new Restaurant())
                ->setName($faker->company())
                ->setDescription($faker->text())
                ->setMaxGuest(random_int(10, 50))
                ->setOwner($this->getReference(UserFixtures::USER_REFERENCE.random_int(1, UserFixtures::USER_NB_TUPLES), User::class))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($restaurant);
            // To create a reference for each restaurant, in order to do the relation with the Picture table 
            $this->addReference(SELF::RESTAUTANT_REFERENCE . $i, $restaurant);
        }
        $manager->flush();
    }

    public function getDependencies():array
    {
        return[UserFixtures::class];
    }
}
