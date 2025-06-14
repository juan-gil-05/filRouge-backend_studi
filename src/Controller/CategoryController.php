<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[ROUTE("api/category", name: 'api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CategoryRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        // To get the content of the request and deserialize it into a category object 
        $category = $this->serializer->deserialize($request->getContent(), Category::class, 'json');
        $category->setCreatedAt(new DateTimeImmutable());

        // To save data into DB
        $this->manager->persist($category);

        // To send data to DB
        $this->manager->flush();

        // To serialize the object into Json, and send it as the response
        $responseData = $this->serializer->serialize($category, 'json');
        // To redirect the client into the page with the new category
        $location = $this->urlGenerator->generate(
            "api_category_show",
            ['id' => $category->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    public function show($id): JsonResponse
    {
        // To search the object by ID
        $category = $this->repository->findOneBy(["id" => $id]);

        if ($category) {
            // To serialize the category object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($category, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    public function edit($id, Request $request): Response
    {
        // To search the object by ID
        $category = $this->repository->findOneBy(["id" => $id]);

        if ($category) {
            // To get the content sent and deserialize it into a category object
            $this->serializer->deserialize(
                $request->getContent(),
                Category::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $category]
            );
            $category->setUpdatedAt(new DateTimeImmutable());

            // To update the DB
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
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
