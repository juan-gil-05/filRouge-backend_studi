<?php

namespace App\DataFixtures;

use App\DataFixtures\RestaurantFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{

    public const RESERVATION_NB_TUPLES = 20;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 1; $i <= self::RESERVATION_NB_TUPLES; $i++) {
            $reservation = (new Reservation())
                ->setGuestNumber($faker->randomNumber(2))
                ->setDate($faker->dateTime())
                ->setHour($faker->time())
                ->setClient($this->getReference(UserFixtures::USER_REFERENCE . random_int(1, UserFixtures::USER_NB_TUPLES), User::class))
                ->setRestaurant($this->getReference(RestaurantFixtures::RESTAUTANT_REFERENCE . random_int(1, RestaurantFixtures::RESTAURANT_NB_TUPLES), Restaurant::class))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($reservation);
        }

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [UserFixtures::class, RestaurantFixtures::class];
    }
}
