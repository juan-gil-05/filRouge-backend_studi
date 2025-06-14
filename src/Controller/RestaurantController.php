<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ROUTE('api/restaurant', name: 'api_restaurant_')]
final class RestaurantController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private RestaurantRepository $repository) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    public function new(): Response
    {

        $restaurant = new Restaurant;

        $restaurant->setName("Quai Antique");
        $restaurant->setDescription("Le meilleur restaurant du nord");
        $restaurant->setAmOpeningTime(['am' => 10]);
        $restaurant->setPmOpeningTime(['pm' => 17]);
        $restaurant->setMaxGuest(50);
        $restaurant->setCreatedAt(new DateTimeImmutable());

        // To save data into DB
        $this->manager->persist($restaurant);

        // To send data to DB
        $this->manager->flush();

        return $this->json(
            [
                "message" => "Restaurant resource created with {$restaurant->getId()} id"
            ],
            Response::HTTP_CREATED,
        );
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    public function show($id): Response
    {

        $restaurant = $this->repository->findOneBy(["id" => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        return $this->json(
            ['message' => "A restaurant was found with the name: {$restaurant->getName()} with {$restaurant->getId()} id"]
        );
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    public function edit($id): Response
    {

        $restaurant = $this->repository->findOneBy(["id" => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $restaurant->setDescription("UPDATED 2");
        $this->manager->flush();

        return $this->json(
            ['message' => "A restaurant was updated: {$restaurant->getDescription()} with {$restaurant->getId()} id"]
        );
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    public function delete($id): Response
    {

        $restaurant = $this->repository->findOneBy(["id" => $id]);

        if (!$restaurant) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $this->manager->remove($restaurant);
        $this->manager->flush();

        return $this->json(
            ['message' => "A restaurant was deleted with id"], 
            Response::HTTP_NO_CONTENT
        );
    }
}
