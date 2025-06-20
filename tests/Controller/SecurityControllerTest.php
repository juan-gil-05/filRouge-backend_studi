<?php

namespace App\Tests\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    public function testUserRegisterUrlSuccessful(): void
    {
        $client = self::createClient();

        $client->request(
            "Post",
            "/api/registration",
            [],
            [],
            ["CONTENT-TYPE" => "application/json"],
            json_encode([
                "email" => "adresse@email.com",
                "password" => "Mot de passe",
                "firstName" => "Juan",
                "lastName" => "Gil",
                "guestNumber" => 15,
                "createdAt" => new DateTimeImmutable()->format("d-m-Y H:i")
            ])
        );

        self::assertResponseIsSuccessful();
    }



    public function testUserLoginUrlSuccessful(): void
    {
        $client = self::createClient();


        $client->request(
            "Post",
            "/api/login",
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            json_encode([
                "username" => "adresse@email.com",
                "password" => "Mot de passe"
            ])
        );

        self::assertResponseIsSuccessful();
    }
}
