<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/security', name: 'api_security_')]
final class SecurityController extends AbstractController
{
    // Je regroupe les services partagés pour garder un contrôleur testable et lisible.
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer
    ) {
    }
    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            description: "Données de l'utilisateur à inscrire",
            required: true,
            content: new OA\JsonContent(ref: new Model(type: User::class, groups: ["user:write"]))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(
                            property: "role",
                            type: "array",
                            items: new OA\Items(type: "string", example: "ROLE_USER")
                        )
                    ],
                    type: "object"
                )
            ),
            new OA\Response(response: 400, description: 'Requête invalide')
        ]
    )]
    #[OA\Tag(name: "security")]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        // Je désérialise la requête en entité User en utilisant le groupe d'écriture défini.
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            ['groups' => ['user:write']]
        );

        // Je sécurise immédiatement le mot de passe et j'enregistre les métadonnées.
        $user->setUuid('TODO_UUID_A_INTEGRER_'.$user->getEmail());
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());

        // Je persiste l'utilisateur puis je flush pour générer l'ID/le token.
        $this->manager->persist($user);
        $this->manager->flush();

        // Je renvoie les infos utiles au front (identifiant, token API, rôles).
        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'role' => $user->getRoles(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        summary: 'Connexion utilisateur',
        requestBody: new OA\RequestBody(
            description: 'Identifiants de connexion',
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['user', 'password'],
                properties: [
                    new OA\Property(property: 'user', type: 'string', example: 'test@0.fr'),
                    new OA\Property(property: 'password', type: 'string', example: '123456789')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'string', example: 'test@0.fr'),
                        new OA\Property(
                            property: 'apiToken',
                            type: 'string',
                            example: '6123b8aed0a87afda185c86fe92eb9187edbdfec'
                        ),
                        new OA\Property(
                            property: 'role',
                            type: 'array',
                            items: new OA\Items(type: 'string', example: 'ROLE_USER')
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Utilisateur introuvable ou credentials invalides',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string', example: 'User not found')],
                    type: 'object'
                )
            )
        ]
    )]
    #[OA\Tag(name: 'security')]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {

        if (null === $user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'role' => $user->getRoles(),
        ], Response::HTTP_OK);
    }
}