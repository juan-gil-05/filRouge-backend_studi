<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ROUTE("api/food", name:'api_food_')]
final class FoodController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private FoodRepository $repository) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    public function new(): Response
    {

        $food = new Food;

        $food->setTitle("Raclette");
        $food->setDescription("A french food");
        $food->setPrice(10);
        $food->setCreatedAt(new DateTimeImmutable());

        // To save data into DB
        $this->manager->persist($food);

        // To send data to DB
        $this->manager->flush();

        return $this->json(
            [
                "message" => "new food created with {$food->getId()} id"
            ],
            Response::HTTP_CREATED,
        );
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    public function show($id): Response
    {

        $food = $this->repository->findOneBy(["id" => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No food found for {$id} id");
        }

        return $this->json(
            ['message' => "A food was found with the name: {$food->getTitle()} with {$food->getId()} id"]
        );
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    public function edit($id): Response
    {

        $food = $this->repository->findOneBy(["id" => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No food found for {$id} id");
        }

        $food->setDescription("UPDATED 2");
        $this->manager->flush();

        return $this->json(
            ['message' => "A food was updated: {$food->getDescription()} with {$food->getId()} id"]
        );
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    public function delete($id): Response
    {

        $food = $this->repository->findOneBy(["id" => $id]);

        if (!$food) {
            throw $this->createNotFoundException("No food found for {$id} id");
        }

        $this->manager->remove($food);
        $this->manager->flush();

        return $this->json(
            ['message' => "A food was deleted with id"], 
            Response::HTTP_NO_CONTENT
        );
    }
}
