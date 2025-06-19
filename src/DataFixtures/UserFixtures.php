<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public const USER_NB_TUPLES = 20;
    public const USER_REFERENCE = "user";

    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 1; $i <= self::USER_NB_TUPLES; $i++) {
            $user = (new User())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->unique()->safeEmail())
                ->setGuestNumber($faker->numberBetween(0, 10))
                ->setCreatedAt(new DateTimeImmutable());

            $user->setPassword($this->passwordHasher->hashPassword($user, "password $i"));

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE.$i, $user);
        }
        $manager->flush();
    }
}