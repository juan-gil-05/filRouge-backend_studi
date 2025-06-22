<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use App\Repository\RestaurantRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;


#[ROUTE("api/picture", name: "api_picture_")]
class PictureController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $manager,
        private RestaurantRepository $restaurantRepo,
        private PictureRepository $pictureRepo
    ) {}

    // Create
    #[ROUTE("/", name: "new", methods: "POST")]
    #[
        OA\post(
            path: "/api/picture/",
            summary: "Register a new picture",
            requestBody: new OA\RequestBody(
                required: true,
                description: "Picture data required to register",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "foodImage"),
                        new OA\Property(property: "slug", type: "string", example: "image_slug"),
                        new OA\Property(property: "restaurantId", type: "int", example: 41, description: "Here you have to use a valid restaurant id"),
                    ],
                    type: "object"
                )
            ),
            responses: [
                new OA\Response(
                    response: 201,
                    description: "Picture registered successfuly",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: "name", type: "string", example: "picture name"),
                            new OA\Property(property: "slug", type: "string", example: "picture_slug"),
                            new OA\Property(property: "restaurantId", type: "int", example: 41, description: "Here you have to use a valid restaurant id"),
                        ],
                        type: "object"
                    )
                )
            ]
        )
    ]
    public function new(Request $request): JsonResponse
    {

        $picture = $this->serializer->deserialize($request->getContent(), Picture::class, "json");
        $picture->setCreatedAt(new DateTimeImmutable());

        $data = json_decode($request->getContent(), true);
        $restaurantId = $data["restaurantId"];
        if (!$restaurantId) {
            return new JsonResponse(["error" => "Restaurant ID is required"], Response::HTTP_BAD_REQUEST);
        }

        $restaurantData = $this->restaurantRepo->find($restaurantId);
        if (!$restaurantData) {
            return new JsonResponse(["error" => "Restaurant not found"], Response::HTTP_NOT_FOUND);
        }

        $picture->setRestaurant($restaurantData);

        $this->manager->persist($picture);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($picture, "json", ["groups" => ["Picture:read"]]);

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    // Show
    #[ROUTE("/{id}", name: "show", methods: "GET")]
    #[OA\Get(
        path: '/api/picture/{id}',
        summary: 'Show a picture',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Picture found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'int', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'image Name'),
                        new OA\Property(property: 'restaurantId', type: 'int', example: 10),

                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Picture not found',

            )
        ]
    )]
    public function show($id): JsonResponse
    {
        $picture = $this->pictureRepo->findBy(["id" => $id]);

        if (!$picture) {
            return new JsonResponse(["error" => "Picture not found"], Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($picture, 'json', ["groups" => ["Picture:read"]]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }
}
