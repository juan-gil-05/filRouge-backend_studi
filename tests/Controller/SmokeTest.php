<?php

namespace App\Tests\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;

class SmokeTest extends WebTestCase
{

    public function testApiDocUrlSuccessful(): void
    {
        $client = self::createClient();
        $client->request("Get", "/api/doc");

        self::assertResponseIsSuccessful();
    }

    public function testApiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->request("Get", "/api/account/profil");

        self::assertResponseStatusCodeSame("401");
    }


    public static function allApiUrl(): array
    {
        return [
            ["Get", "/api/doc"],
            ["Post", "/api/category/"],
            ["Get", "/api/category/{id}"],
        ];
    }
}
