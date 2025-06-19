<?php

namespace App\Tests\Entity;

use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTest extends TestCase
{

    public function testTheAutomaticApiTokenSettingWhenAnUserIsCreated(): void
    {
        $user = new User;

        $this->assertNotNull($user->getApiToken());
    }

    public function testTheUserHasAtLeastOneRole(): void
    {
        $user = new User;
        $this->assertNotEmpty($user->getRoles());
    }

    public function testPasswordIsNotStoredInPlainText(): void
    {

        $user = new User;

        $plainPassword = "password123";

        $passwordHashed = password_hash($plainPassword, PASSWORD_BCRYPT);

        $user->setPassword($passwordHashed);

        $this->assertNotSame($plainPassword, $user->getPassword());
    }

    public function testUserHasCreatedAtProperty():void
    {
        $user = new User;

        $user->setCreatedAt(new DateTimeImmutable());

        $this->assertNotEmpty($user->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $user->getCreatedAt());

    }

}
