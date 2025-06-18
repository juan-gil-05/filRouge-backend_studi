<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase {

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

}