<?php

namespace App\Controller;

use App\Entity\Picture;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;


#[ROUTE("api/picture", name : "api_picture_")]
class PictureController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $manager
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

        $this->manager->persist($picture);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($picture, "json");

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }
}
