<?php

namespace App\Controller;

use App\Entity\Food;
use App\Repository\FoodRepository;
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
use OpenApi\Attributes as OA;

#[ROUTE("api/food", name: 'api_food_')]
final class FoodController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private FoodRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    #[OA\Post(
        path: '/api/food/',
        summary: "Register a new Food",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Food data required to register",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "bourguignon"),
                    new OA\Property(property: "description", type: "string", example: "The best Food"),
                    new OA\Property(property: "price", type: "string", example: "1.50"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Food registered success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Food name"),
                        new OA\Property(property: "description", type: "string", example: "Food description"),
                        new OA\Property(property: "price", type: "string", example: "1.50"),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        // To get the content of the request and deserialize it into a food object 
        $food = $this->serializer->deserialize($request->getContent(), Food::class, 'json');
        $food->setCreatedAt(new DateTimeImmutable());

        // To save data into DB
        $this->manager->persist($food);

        // To send data to DB
        $this->manager->flush();

        // To serialize the object into Json, and send it as the response
        $responseData = $this->serializer->serialize($food, 'json');
        // To redirect the client into the page with the new food
        $location = $this->urlGenerator->generate(
            "api_food_show",
            ['id' => $food->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // Read By id
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/food/{id}',
        summary: "Show a Food",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'Food ID to show',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Food found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Food name"),
                        new OA\Property(property: "description", type: "string", example: "Food description"),
                        new OA\Property(property: "price", type: "string", example: "1.50"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Food not found",
            )
        ]
    )]
    public function show($id): JsonResponse
    {
        // To search the object by ID
        $food = $this->repository->findOneBy(["id" => $id]);

        if ($food) {
            // To serialize the food object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($food, 'json', ["groups" => ["Food:read"]]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Read all
    #[ROUTE("/all", name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/food/all',
        summary: "Show all Foods",
        responses: [
            new OA\Response(
                response: 200,
                description: "Food found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Food name"),
                        new OA\Property(property: "description", type: "string", example: "Food description"),
                        new OA\Property(property: "price", type: "string", example: "1.50"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Food not found",
            )
        ]
    )]
    public function showAll(): JsonResponse
    {
        // To search the object by ID
        $food = $this->repository->findAll();

        if ($food) {
            // To serialize the food object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($food, 'json', ["groups" => ["Food:read"]]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/food/{id}',
        summary: "Edit a Food",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Food data to edit",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property("title", type: "string", example: "NEW Food name"),
                    new OA\Property(property: "description", type: "string", example: "NEW Food description"),
                    new OA\Property(property: "price", type: "string", example: "9.20"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Food edited successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Food name"),
                        new OA\Property(property: "description", type: "string", example: "Food description"),
                        new OA\Property(property: "price", type: "string", example: "9.20"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Food not found",
            )
        ]
    )]
    public function edit($id, Request $request): Response
    {
        // To search the object by ID
        $food = $this->repository->findOneBy(["id" => $id]);

        if ($food) {
            // To get the content sent and deserialize it into a food object
            $this->serializer->deserialize(
                $request->getContent(),
                Food::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $food]
            );
            $food->setUpdatedAt(new DateTimeImmutable());

            // To update the DB
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/food/{id}',
        summary: "Delete a Food",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'Food ID to Delete',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Food deleted successfully",
            ),
            new OA\Response(
                response: 404,
                description: "Food not found",
            )
        ]

    )]
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
