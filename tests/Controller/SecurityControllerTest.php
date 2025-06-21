<?php

namespace App\Tests\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{

    private KernelBrowser $client;

    private string $apiToken;
    
    // Initial function to set up the test client with user registration and login in order to get teh Api Token  
    protected function setUp(): void
    {
        $this->client = self::createClient();

        $this->UserRegister();

        $this->apiToken = $this->UserLoginAndGetApiToken();
    }

    public function UserRegister(): void
    {
        $createdAt = (new DateTimeImmutable())->format("d-m-Y H:i");

        $this->client->request(
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
                "createdAt" => $createdAt
            ])
        );

        self::assertResponseStatusCodeSame(201);
    }



    public function UserLoginAndGetApiToken(): string
    {

        $this->client->request(
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

        $responseData = json_decode($this->client->getResponse()->getContent());

        $userApiToken = $responseData->apiToken ?? null;

        self::assertNotNull($userApiToken);

        return $userApiToken;
    }

    public function testUserShowProfilSuccesful(): void
    {

        $this->client->request(
            "Get",
            "/api/account/profil?firstName=Juan",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->apiToken
            ],
        );

        $this->assertResponseIsSuccessful();
    }
}
