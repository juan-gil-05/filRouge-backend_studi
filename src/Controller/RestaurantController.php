<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
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
use Symfony\Component\Serializer\Encoder\JsonDecode;

#[ROUTE('api/restaurant', name: 'api_restaurant_')]
final class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $restaurantRepo,
        private UserRepository $userRepo,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    #[OA\Post(
        path: '/api/restaurant/',
        summary: "Register a new restaurant",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Restaurant data required to register",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Quai Antique"),
                    new OA\Property(property: "description", type: "string", example: "The best restaurant"),
                    new OA\Property(property: "maxGuest", type: "int", example: 10),
                    new OA\Property(property: "owner", type: "int", example: 54, description:"Here you have to use a valid user id"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Restaurant registered success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Restaurant name"),
                        new OA\Property(property: "description", type: "string", example: "Restaurant description"),
                        new OA\Property(property: "maxGuest", type: "int", example: 10),
                        new OA\Property(property: "owner", type: "int", example: 54, description:"Here you have to use a valid user id"),

                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        // To get the content of the request and deserialize it into a Restaurant object 
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
        $restaurant->setCreatedAt(new DateTimeImmutable());

        $data = json_decode($request->getContent(), true);
        $ownerId = $data["owner"];

        if (!$ownerId) {
            return new JsonResponse(["error" => "owner ID is required"], Response::HTTP_BAD_GATEWAY);
        }

        $ownerData = $this->userRepo->find($ownerId);

        if (!$ownerData) {
            return new JsonResponse(["error" => "owner not found"], Response::HTTP_NOT_FOUND);
        }

        $restaurant->setOwner($ownerData);

        // To save data into DB
        $this->manager->persist($restaurant);

        // To send data to DB
        $this->manager->flush();

        // To serialize the object into Json, and send it as the response
        $responseData = $this->serializer->serialize($restaurant, 'json', ['groups' => ['Restaurant:read']]);
        // To redirect the client into the page with the new restaurant
        $location = $this->urlGenerator->generate(
            "api_restaurant_show",
            ['id' => $restaurant->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/restaurant/{id}',
        summary: "Show a restaurant",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'Restaurant ID to show',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Restaurant found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Restaurant name"),
                        new OA\Property(property: "description", type: "string", example: "Restaurant description"),
                        new OA\Property(property: "maxGuest", type: "int", example: "max number of guests"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Restaurant not found",
            )
        ]
    )]
    public function show($id): JsonResponse
    {
        // To search the object by ID
        $restaurant = $this->restaurantRepo->findOneBy(["id" => $id]);

        if ($restaurant) {
            // To serialize the Restaurant object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($restaurant, 'json', ['groups' => ['Restaurant:read']]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/restaurant/{id}',
        summary: "Edit a restaurant",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'Restaurant ID to Edit',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Restaurant data to edit",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "NEW Restaurant name"),
                    new OA\Property(property: "description", type: "string", example: "NEW Restaurant description"),
                    new OA\Property(property: "maxGuest", type: "int", example: "5"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Restaurant edited successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Restaurant name edited"),
                        new OA\Property(property: "description", type: "string", example: "Restaurant description edited"),
                        new OA\Property(property: "maxGuest", type: "int", example: "10"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Restaurant not found",
            )
        ]
    )]
    public function edit($id, Request $request): Response
    {
        // To search the object by ID
        $restaurant = $this->restaurantRepo->findOneBy(["id" => $id]);

        if ($restaurant) {
            // To get the content sent and deserialize it into a Restaurant object
            $this->serializer->deserialize(
                $request->getContent(),
                Restaurant::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
            );
            $restaurant->setUpdatedAt(new DateTimeImmutable());

            // To update the DB
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/restaurant/{id}',
        summary: "Delete a restaurant",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'Restaurant ID to Delete',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Restaurant deleted successfully",
            ),
            new OA\Response(
                response: 404,
                description: "Restaurant not found",
            )
        ]

    )]
    public function delete($id): Response
    {

        $restaurant = $this->restaurantRepo->findOneBy(["id" => $id]);

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
