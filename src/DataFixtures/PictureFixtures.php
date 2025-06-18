<?php

namespace App\DataFixtures;

use App\Entity\Picture;
use App\Entity\Restaurant;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PictureFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i <= 20; $i++) {
            $picture = (new Picture())
                ->setName("Picture n°$i")
                ->setSlug("slug_$i")
                // Foreign key
                ->setRestaurant($this->getReference("restaurant" . random_int(1, 20), Restaurant::class))
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
