<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RestaurantFixtures extends Fixture
{


    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i <= 20; $i++) {
            $restaurant = (new Restaurant())
                ->setName("Restaurant n°$i")
                ->setDescription("Description restaurant n°$i")
                ->setMaxGuest(random_int(10, 50))
                ->setCreatedAt(new DateTimeImmutable());

            $manager->persist($restaurant);
            // To create a reference for each restaurant, in order to do the relation with the Picture table 
            $this->addReference("restaurant" . $i, $restaurant);
        }
        $manager->flush();
    }
}
