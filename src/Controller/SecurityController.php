<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/security', name: 'api_api_')]
final class SecurityController extends AbstractController
{
    // Je regroupe les services partagés pour garder un contrôleur testable et lisible.
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
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