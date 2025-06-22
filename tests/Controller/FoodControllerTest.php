<?php

namespace App\Tests\Controller;

use App\Entity\Food;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class FoodControllerTest extends WebTestCase
{
    private $databaseTool;
    private \Doctrine\Common\DataFixtures\ReferenceRepository $referenceRepository;
    private KernelBrowser $client;
    private string $apiToken;
    private int $foodId;

    // Initial function to set up the test client with user registration and login in order to get teh Api Token  
    protected function setUp(): void
    {
        // To turn off the kernel in order to avoid any error
        self::ensureKernelShutdown();
        $this->client = self::createClient();

        // Use of TestFixtureBundle to initialize the DB after every test
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $this->databaseTool->loadFixtures([
            \App\DataFixtures\UserFixtures::class,
            \App\DataFixtures\CategoryFixtures::class,
            \App\DataFixtures\FoodFixtures::class,
        ]);
        // Repository to get the data from the fixture
        $this->referenceRepository = $fixtures->getReferenceRepository();

        $userData = $this->referenceRepository->getReference("user1", User::class);

        $this->apiToken = $userData->getApiToken();

        $foodData = $this->referenceRepository->getReference("food1", Food::class);

        $this->foodId = $foodData->getId();
    }

    public function testShowRestaurantSuccesful(): void
    {
        $this->client->request(
            "Get",
            "/api/food/{$this->foodId}",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->apiToken
            ],
        );

        $this->assertResponseIsSuccessful();
    }

    public function testEditRestaurantSuccesful(): void
    {
        $updatedAt = (new DateTimeImmutable())->format("d-m-Y H:i");

        $this->client->request(
            "Put",
            "/api/food/{$this->foodId}",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->apiToken
            ],
            json_encode([
                "name" => "NEW Food name",
                "description" => "NEW Food description",
                "price" => "15",
                "updatedAt" => $updatedAt
            ])
        );

        $this->assertResponseStatusCodeSame(204); // HTTP_NO_CONTENT
    }

    public function testDeletRestaurantSuccesful(): void
    {
        $this->client->request(
            "Delete",
            "/api/food/{$this->foodId}",
            [],
            [],
            // To send the api token of the user
            [
                "HTTP_X-AUTH-TOKEN" => $this->apiToken
            ]
        );

        $this->assertResponseStatusCodeSame(204); // HTTP_NO_CONTENT

    }
}
