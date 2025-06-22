<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
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

#[ROUTE('api/reservation', name: 'api_reservation_')]
final class reservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository $restaurantRepo,
        private UserRepository $userRepo,
        private ReservationRepository $reservationRepo,
        private SerializerInterface $serializer,
    ) {}

    // Create
    #[ROUTE("/", name: 'new', methods: 'POST')]
    #[OA\Post(
        path: '/api/reservation/',
        summary: "Register a new reservation",
        requestBody: new OA\RequestBody(
            required: true,
            description: "reservation data required to register",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "guestNumber", type: "int", example: 50),
                    new OA\Property(property: "date", type: "string", example: "2025-10-10"),
                    new OA\Property(property: "hour", type: "string", example: "14:30:00"),
                    new OA\Property(property: "restaurantId", type: "int", example: 70, description: "Here you have to use a valid Restaurant id"),
                    new OA\Property(property: "clientId", type: "int", example: 70, description: "Here you have to use a valid User id"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "reservation registered success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "guestNumber", type: "int", example: 60),
                        new OA\Property(property: "date", type: "string", example: "Date of the reservation"),
                        new OA\Property(property: "hour", type: "string", example: "Hour of the reservation"),
                        new OA\Property(property: "restaurantId", type: "int", example: 70, description: "Here you have to use a valid Restaurant id"),
                        new OA\Property(property: "clientId", type: "int", example: 70, description: "Here you have to use a valid User id"),
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        // To get the content of the request and deserialize it into a reservation object 
        $reservation = $this->serializer->deserialize($request->getContent(), Reservation::class, 'json');
        $reservation->setCreatedAt(new DateTimeImmutable());

        $data = json_decode($request->getContent(), true);
        $clientId = $data['clientId'] ?? null;
        $restaurantId = $data['restaurantId'] ?? null;

        if (!$clientId || !$restaurantId) {
            return new JsonResponse(['error' => 'clientId and restaurantId are required'], Response::HTTP_BAD_REQUEST);
        }

        $clientData = $this->userRepo->find($clientId);
        $restaurantData = $this->restaurantRepo->find($restaurantId);

        if (!$clientData) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }
        if (!$restaurantData) {
            return new JsonResponse(['error' => 'Restaurant not found'], Response::HTTP_NOT_FOUND);
        }

        $reservation->setRestaurant($restaurantData);
        $reservation->setClient($clientData);

        // To save data into DB
        $this->manager->persist($reservation);

        // To send data to DB
        $this->manager->flush();

        // To serialize the object into Json, and send it as the response
        $responseData = $this->serializer->serialize($reservation, 'json', ['groups' => ['Reservation:read']]);

        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    // Read
    #[ROUTE("/{id}", name: 'show', methods: 'GET')]
    #[OA\Get(
        path: '/api/reservation/{id}',
        summary: "Show a reservation",
        responses: [
            new OA\Response(
                response: 200,
                description: "Reservation found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "guestNumber", type: "int", example: 60),
                        new OA\Property(property: "date", type: "string", example: "Date of the reservation"),
                        new OA\Property(property: "hour", type: "string", example: "Hour of the reservation"),
                        new OA\Property(property: "restaurantId", type: "int", example: 70, description: "Here you have to use a valid Restaurant id"),
                        new OA\Property(property: "clientId", type: "int", example: 70, description: "Here you have to use a valid User id"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Reservation not found",
            )
        ]
    )]
    public function show($id): JsonResponse
    {
        // To search the object by ID
        $reservation = $this->reservationRepo->findOneBy(["id" => $id]);

        if ($reservation) {
            // To serialize the reservation object, in order to send it as a JsonResponse 
            $responseData = $this->serializer->serialize($reservation, 'json', ['groups' => ['Reservation:read']]);

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        // If it wasn't found
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Update
    #[ROUTE("/{id}", name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: '/api/reservation/{id}',
        summary: "Edit a reservation",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Reservation data to edit",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "guestNumber", type: "int", example: 10),
                    new OA\Property(property: "date", type: "string", example: "2025-01-07"),
                    new OA\Property(property: "hour", type: "string", example: "10:00:00"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Reservation edited successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "guestNumber", type: "int", example: 10),
                        new OA\Property(property: "date", type: "string", example: "Date Updated"),
                        new OA\Property(property: "hour", type: "string", example: "Hour Updated"),
                    ],
                    type: "object"
                )
            ),
            new OA\Response(
                response: 404,
                description: "Reservation not found",
            )
        ]
    )]
    public function edit($id, Request $request): Response
    {
        // To search the object by ID
        $reservation = $this->reservationRepo->findOneBy(["id" => $id]);

        if ($reservation) {
            // To get the content sent and deserialize it into a reservation object
            $this->serializer->deserialize(
                $request->getContent(),
                Reservation::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $reservation]
            );
            $reservation->setUpdatedAt(new DateTimeImmutable());

            // To update the DB
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    // Delete
    #[ROUTE("/{id}", name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: '/api/reservation/{id}',
        summary: "Delete a reservation",
        responses: [
            new OA\Response(
                response: 204,
                description: "reservation deleted successfully",
            ),
            new OA\Response(
                response: 404,
                description: "reservation not found",
            )
        ]

    )]
    public function delete($id): Response
    {

        $reservation = $this->reservationRepo->findOneBy(["id" => $id]);

        if (!$reservation) {
            throw $this->createNotFoundException("No Restaurant found for {$id} id");
        }

        $this->manager->remove($reservation);
        $this->manager->flush();

        return $this->json(
            ['message' => "A reservation was deleted with id"],
            Response::HTTP_NO_CONTENT
        );
    }
}
