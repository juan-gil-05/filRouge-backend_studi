<?php

namespace App\Tests\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class SecurityControllerTest extends WebTestCase
{
    private $databaseTool;
    private \Doctrine\Common\DataFixtures\ReferenceRepository $referenceRepository;
    private KernelBrowser $client;
    private object $userData;
    private string $apiToken;
    private int $userId;
    private string $firstName;

    // Initial function to set up the test client with user registration and login in order to get teh Api Token  
    protected function setUp(): void
    {
        // To turn off the kernel in order to avoid any error
        self::ensureKernelShutdown();
        $this->client = self::createClient();

        // Use of TestFixtureBundle to initialize the DB after every test
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $this->databaseTool->loadFixtures([
            \App\DataFixtures\UserFixtures::class
        ]);
        // Repository to get the data from the fixture
        $this->referenceRepository = $fixtures->getReferenceRepository();

        $this->userData = $this->UserLoginAndGetUserData();

        $this->firstName = $this->userData->firstName;
        $this->userId = $this->userData->userId;
    }

    public function UserLoginAndGetUserData(): object
    {

        /**@var App\Entity\User $user  */
        $user = $this->referenceRepository->getReference('user1', User::class); // to get the first user created by the fixtures 

        $this->client->request(
            "Post",
            "/api/login",
            [],
            [],
            ["CONTENT_TYPE" => "application/json"],
            json_encode([
                "username" => $user->getEmail(),
                "password" => "password1"
            ])
        );

        $responseData = json_decode($this->client->getResponse()->getContent());

        $userApiToken = $responseData->apiToken ?? null;

        self::assertNotNull($userApiToken);

        return $responseData;
    }

    public function testUserShowProfilSuccesful(): void
    {
        $this->client->request(
            "Get",
            "/api/account/profil?firstName={$this->firstName}",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->userData->apiToken
            ],
        );

        $this->assertResponseIsSuccessful();
    }

    public function testEditUserSuccesful(): void
    {
        $updatedAt = (new DateTimeImmutable())->format("d-m-Y H:i");

        $this->client->request(
            "Put",
            "/api/account/edit/{$this->userId}",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->userData->apiToken
            ],
            json_encode([
                "email" => "adresse@email.com",
                "password" => "Mot de passe",
                "firstName" => "Juan",
                "lastName" => "Gil",
                "guestNumber" => 15,
                "updatedAt" => $updatedAt
            ])
        );

        $this->assertResponseStatusCodeSame(204); // HTTP_NO_CONTENT
    }
}
