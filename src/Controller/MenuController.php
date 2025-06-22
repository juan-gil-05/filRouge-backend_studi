<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\RestaurantRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[ROUTE('api/menu', name: 'api_menu_')]
final class MenuController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $restaurantRepo,
        private MenuRepository $menuRepo,
        private SerializerInterface $serializer,
    ) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    #[OA\Post(
        path: '/api/menu/',
        summary: "Register a new menu",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Menu data required to register",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Tasteville"),
                    new OA\Property(property: "description", type: "string", example: "This is the best menu"),
                    new OA\Property(property: "price", type: "string", example: "21.99"),
                    new OA\Property(property: "restaurantId", type: "int", example: 75, description: "Here you have to use a valid Restaurant id"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "menu registered success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Menu title"),
                        new OA\Property(property: "description", type: "string", example: "Menu description"),
                        new OA\Property(property: "price", type: "string", example: "14.99"),
                        new OA\Property(property: "restaurantId", type: "int", example: 75, description: "Here you have to use a valid Restaurant id"),

                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        // To get the content of the request and deserialize it into a menu object 
        $menu = $this->serializer->deserialize($request->getContent(), Menu::class, 'json');
        $menu->setCreatedAt(new DateTimeImmutable());

        $data = json_decode($request->getContent(), true);
        $restaurantId = $data["restaurantId"];

        if (!$restaurantId) {
            return new JsonResponse(["error" => "Restaurant ID is required"], Response::HTTP_BAD_REQUEST);
        }

        $restaurantData = $this->restaurantRepo->find($restaurantId);

        if (!$restaurantData) {
            return new JsonResponse(["error" => "Restaurant not found"], Response::HTTP_NOT_FOUND);
        }

        $menu->setRestaurant($restaurantData);

        // To save data into DB
        $this->manager->persist($menu);

        // To send data to DB
        $this->manager->flush();

        // To serialize the object into Json, and send it as the response
        $responseData = $this->serializer->serialize($menu, 'json', ['groups' => ['Menu:read']]);


        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/menu/{id}',
        summary: "Show a menu",
        responses: [
            new OA\Response(
                response: 200,
                description: "Menu found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Menu name"),
                        new OA\Property(property: "description", type: "string", example: "Menu description"),
                        new OA\Property(property: "price", type: "string", example: "Menu price"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Menu not found",
            )
        ]
    )]
    public function show($id): JsonResponse
    {
        // To search the object by ID
        $menu = $this->menuRepo->findOneBy(["id" => $id]);

        if ($menu) {
            // To serialize the menu object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($menu, 'json', ["groups" => ["Menu:read"]]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/menu/{id}',
        summary: "Edit a menu",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Menu data to edit",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "NEW menu title"),
                    new OA\Property(property: "description", type: "string", example: "NEW menu description"),
                    new OA\Property(property: "price", type: "string", example: "15.50"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Menu edited successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "title", type: "string", example: "Menu title edited"),
                        new OA\Property(property: "description", type: "string", example: "Menu description edited"),
                        new OA\Property(property: "price", type: "string", example: "15.50"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Menu not found",
            )
        ]
    )]
    public function edit($id, Request $request): Response
    {
        // To search the object by ID
        $menu = $this->menuRepo->findOneBy(["id" => $id]);

        if ($menu) {
            // To get the content sent and deserialize it into a menu object
            $this->serializer->deserialize(
                $request->getContent(),
                Menu::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $menu]
            );
            $menu->setUpdatedAt(new DateTimeImmutable());

            // To update the DB
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/menu/{id}',
        summary: "Delete a menu",
        responses: [
            new OA\Response(
                response: 204,
                description: "Menu deleted successfully",
            ),
            new OA\Response(
                response: 404,
                description: "Menu not found",
            )
        ]

    )]
    public function delete($id): Response
    {

        $menu = $this->menuRepo->findOneBy(["id" => $id]);

        if (!$menu) {
            throw $this->createNotFoundException("No menu found for {$id} id");
        }

        $this->manager->remove($menu);
        $this->manager->flush();

        return $this->json(
            ['message' => "A menu was deleted with id"],
            Response::HTTP_NO_CONTENT
        );
    }
}
