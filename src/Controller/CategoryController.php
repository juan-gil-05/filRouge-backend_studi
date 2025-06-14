<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ROUTE("api/category", name:'api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private CategoryRepository $repository) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    public function new(): Response
    {

        $category = new Category;

        $category->setTitle("Dessert");
        $category->setCreatedAt(new DateTimeImmutable());

        // To save data into DB
        $this->manager->persist($category);

        // To send data to DB
        $this->manager->flush();

        return $this->json(
            [
                "message" => "category created with {$category->getId()} id"
            ],
            Response::HTTP_CREATED,
        );
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    public function show($id): Response
    {

        $category = $this->repository->findOneBy(["id" => $id]);

        if (!$category) {
            throw $this->createNotFoundException("No category found for {$id} id");
        }

        return $this->json(
            ['message' => "A category was found with the name: {$category->getTitle()} with {$category->getId()} id"]
        );
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    public function edit($id): Response
    {

        $category = $this->repository->findOneBy(["id" => $id]);

        if (!$category) {
            throw $this->createNotFoundException("No category found for {$id} id");
        }

        $category->setTitle("UPDATED 2");
        $this->manager->flush();

        return $this->json(
            ['message' => "A category was updated: {$category->getId()} id"]
        );
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    public function delete($id): Response
    {

        $category = $this->repository->findOneBy(["id" => $id]);

        if (!$category) {
            throw $this->createNotFoundException("No category found for {$id} id");
        }

        $this->manager->remove($category);
        $this->manager->flush();

        return $this->json(
            ['message' => "A category was deleted with id"], 
            Response::HTTP_NO_CONTENT
        );
    }
}
