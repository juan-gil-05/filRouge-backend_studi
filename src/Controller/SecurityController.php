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
        private SerializerInterface $serializer
    ) {}

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: '/api/registration',
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
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
                description: "Utilisateur inscrit avec succès",
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
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // To qet the content and transforme it into an User object
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        // To hash the password
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
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
    #[Route('/account/profil', name: 'account_profil', methods: ['POST'])]
    public function profil(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');

        $user = $this->repository->findOneBy(["firstName" => $user->getFirstName()]);

        if ($user) {
            $responseData = $this->serializer->serialize($user, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(["message" => "Any user found"], Response::HTTP_NOT_FOUND);
    }

    // Function to edit the user profil
    #[Route('/account/edit/{id}', name: 'account_edit', methods: ['PUT'])]
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
            $user->setUpdatedAt(new DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(["message" => "Any user to edit found"], Response::HTTP_NOT_FOUND);
    }
}
