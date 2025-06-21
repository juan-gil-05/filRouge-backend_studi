<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;


#[Route("/api", name: 'app_api_')]
final class SecurityController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $manager,
        private UserRepository $repository,
        private SerializerInterface $serializer,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: '/api/registration',
        summary: "Register a new user",
        requestBody: new OA\RequestBody(
            required: true,
            description: "user data required to register",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe"),
                    new OA\Property(property: "firstName", type: "string", example: "Juan"),
                    new OA\Property(property: "lastName", type: "string", example: "Gil"),
                    new OA\Property(property: "guestNumber", type: "int", example: "15"),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User registered success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER"))
                    ],
                    type: "object"
                )
            )
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        // To get the content and transforme it into an User object
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        // To hash the password
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());

        // To save the user into DB
        $this->manager->persist($user);
        // To send data to DB
        $this->manager->flush();

        return new JsonResponse([
            "user" => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'role' => $user->getRoles()
        ], Response::HTTP_CREATED);
    }

    // Function to Login 
    #[Route('/login', name: 'login', methods: ['POST'])]
    #[
        OA\Post(
            path: '/api/login',
            summary: 'Login with a user',
            requestBody: new OA\RequestBody(
                required: true,
                description: "User data required to login",
                // The content with the data that we must send in the request
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "username", type: "string", example: "adresse@email.com"),
                        new OA\Property(property: "password", type: "string", example: "Mot de passe"),
                    ]
                )
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Loged in success",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(property: "user", type: "string", example: "Juanito"),
                            new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                            new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string",  example: "ROLE_USER")),
                        ]
                    )
                )
            ]
        )

    ]
    public function login(#[CurrentUser()] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user'  => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    // Function to see the user profil
    #[Route('/account/profil', name: 'account_profil', methods: ['GET'])]
    #[OA\Get(
        path: '/api/account/profil',
        summary: "Show User",
        parameters: [
            new OA\Parameter(
                name: 'firstName',
                in: 'query',
                required: true,
                description: "Find user by User first name",
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property("id", type: 'int', example: 123),
                        new OA\Property("email", type: 'string', example: "User email"),
                        new OA\Property("userIdentifier", type: 'string', example: "User identifier"),
                        new OA\Property("password", type: 'string', example: "$2y$13TBOzSmOit2w8JgzriUQPocdhYWe/VYUf1a"),
                        new OA\Property("apiToken", type: 'string', example: "ba253a56c8c76de85031ad7acbdaf23a4d71b488"),
                        new OA\Property("firstName", type: 'string', example: "User first name"),
                        new OA\Property("lastName", type: 'string', example: "User last name"),
                        new OA\Property("guestNumber", type: 'int', example: 14),
                        new OA\Property("allergy", type: 'string', example: "User allergies"),

                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
            )
        ]
    )]
    public function profil(Request $request): JsonResponse
    {
        $firstName = $request->query->get('firstName');

        $user = $this->repository->findOneBy(["firstName" => $firstName]);

        if ($user) {
            $responseData = $this->serializer->serialize($user, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Any user found"], Response::HTTP_NOT_FOUND);
    }

    // Function to edit the user profil
    #[Route('/account/edit/{id}', name: 'account_edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/account/edit/{id}',
        summary: "Edit a user",
        // The parameters section is already called autommatically
        // parameters: [
        //     new OA\Parameter(
        //         name: 'id',
        //         in: 'query',
        //         required:true,
        //         description: 'User ID to Edit',
        //         schema: new OA\Schema(type: 'integer')
        //     )
        // ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "user data required to edit",
            // The content with the data that we must send in the request
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", example: "newAdresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "New Mot de passe"),
                    new OA\Property(property: "firstName", type: "string", example: "Juanito"),
                    new OA\Property(property: "lastName", type: "string", example: "Gil"),
                    new OA\Property(property: "guestNumber", type: "int", example: 7),
                ],
                type: "object"
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "User edited successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property("id", type: 'int', example: 123),
                        new OA\Property("email", type: 'string', example: "new User email"),
                        new OA\Property("userIdentifier", type: 'string', example: "new User identifier"),
                        new OA\Property("password", type: 'string', example: "$2y$13TBOzSmOit2w8JgzriUQPocdhYWe/VYUf1a"),
                        new OA\Property("apiToken", type: 'string', example: "ba253a56c8c76de85031ad7acbdaf23a4d71b488"),
                        new OA\Property("firstName", type: 'string', example: "new User first name"),
                        new OA\Property("lastName", type: 'string', example: "new User last name"),
                        new OA\Property("guestNumber", type: 'int', example: 14),
                        new OA\Property("allergy", type: 'string', example: "new User allergies"),

                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: "User not found",
            )
        ]


    )]
    public function edit(Request $request, $id): JsonResponse
    {

        $user = $this->repository->findOneBy(["id" => $id]);

        if ($user) {
            $user = $this->serializer->deserialize(
                $request->getContent(),
                User::class,
                'json',
                // To update the existing object instead of creating a new one
                [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
            );
            // To hash the password
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
            $user->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(["message" => "Any user to edit found"], Response::HTTP_NOT_FOUND);
    }
}
