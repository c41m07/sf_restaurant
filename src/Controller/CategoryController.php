<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

// Je gère ici toutes les opérations CRUD exposées aux clients pour la ressource Category.
#[Route('api/category', name: 'api_category_')]
final class CategoryController extends AbstractController
{
    // Je regroupe les services nécessaires dans le constructeur pour simplifier l’injection.
    public function __construct(
        private EntityManagerInterface $manager,
        private CategoryRepository     $repository,
        private RestaurantRepository   $restaurantRepository,
        private SerializerInterface    $serializer,
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Get(
        summary: "Lister toutes les catégories",
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des catégories',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:list']))
                )
            )
        ]
    )]
    #[OA\Tag(name: 'category')]
    #[Security(name: 'Bearer')]
    public function index(): JsonResponse
    {
        // Je récupère toutes les catégories puis les sérialise avec le groupe list
        $categories = $this->repository->findAll();
        $payload = $this->serializer->serialize($categories, 'json', ['groups' => ['category:list']]);

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
    #[OA\Post(
        summary: 'Créer une catégorie',
        requestBody: new OA\RequestBody(
            description: 'Payload de création',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:write']))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Catégorie créée',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'catégorie créée avec succès 1 id')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Requête invalide'),
            new OA\Response(response: 401, description: 'Authentification requise')
        ]
    )]
    #[OA\Tag(name: 'category')]
    #[Security(name: 'Bearer')]
    public function new(Request $request): JsonResponse
    {
        // Je désérialise le JSON entrant pour créer une nouvelle catégorie
        $category = $this->serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json',
            ['groups' => ['category:write']]
        );

        $category->setUuid('TODO_UUID_A_INTEGRER_' . $category->getTitle());
        $category->setRestaurant($this->restaurantRepository->find($this->getUser()->getRestaurant()->getId()));
        $category->setCreatedAt(new \DateTime());
        $this->applyRestaurantRelation($category, $request);

        $this->manager->persist($category);
        $this->manager->flush();

        return $this->json(
            ['message' => "catégorie créée avec succès {$category->getId()} id"],
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    #[OA\Get(
        summary: 'Afficher une catégorie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Identifiant de la catégorie',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catégorie trouvée',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:detail']))
            ),
            new OA\Response(
                response: 404,
                description: 'Catégorie introuvable',
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: 'message', type: 'string')],
                    type: 'object'
                )
            )
        ]
    )]
    #[OA\Tag(name: 'category')]
    public function show(int $id): JsonResponse
    {
        // Je récupère la catégorie demandée
        $category = $this->repository->find($id);

        // Si je ne trouve rien, je renvoie une 404 claire
        if (!$category) {
            throw new NotFoundHttpException("Catégorie d'id {$id} introuvable");
        }

        // Je renvoie les infos principales via le Serializer pour rester cohérent
        $payload = $this->serializer->serialize($category, 'json', ['groups' => ['category:detail']]);
        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\\d+'])]
    #[OA\Put(
        summary: 'Mettre à jour une catégorie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Identifiant de la catégorie',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            description: 'Payload de mise à jour',
            required: true,
            content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:write']))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Catégorie mise à jour',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Catégorie mise à jour'),
                        new OA\Property(property: 'id', type: 'integer'),
                        new OA\Property(property: 'title', type: 'string')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Authentification requise'),
            new OA\Response(response: 404, description: 'Catégorie introuvable')
        ]
    )]
    #[OA\Tag(name: 'category')]
    #[Security(name: 'Bearer')]
    public function edit(int $id, Request $request): JsonResponse
    {
        // Je charge l'entité à modifier
        $category = $this->repository->find($id);

        // Je garde la même gestion d'erreur qu'au dessus
        if (!$category) {
            throw new NotFoundHttpException("Catégorie d'id {$id} introuvable");
        }

        // Je désérialise la requête pour mettre à jour l'entité existante
        $this->serializer->deserialize(
            $request->getContent(),
            Category::class,
            'json',
            ['groups' => ['category:write'], 'object_to_populate' => $category]
        );

        $category->setUpdatedAt(new \DateTime());
        $this->applyRestaurantRelation($category, $request);
        $this->manager->flush();

        return $this->json([
            'message' => 'Catégorie mise à jour',
            'id' => $category->getId(),
            'title' => $category->getTitle(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    #[OA\Delete(
        summary: 'Supprimer une catégorie',
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Identifiant de la catégorie',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Catégorie supprimée'
            ),
            new OA\Response(response: 401, description: 'Authentification requise'),
            new OA\Response(response: 404, description: 'Catégorie introuvable')
        ]
    )]
    #[OA\Tag(name: 'category')]
    #[Security(name: 'Bearer')]
    public function delete(int $id): JsonResponse
    {
        // Je vérifie que la catégorie existe avant de supprimer
        $category = $this->repository->find($id);

        // Même logique : si rien trouvé je renvoie 404
        if (!$category) {
            throw new NotFoundHttpException("Catégorie d'id {$id} introuvable");
        }

        // Je supprime l'entité puis j'envoie la requête en base
        $this->manager->remove($category);
        $this->manager->flush();

        // Je renvoie une confirmation simple
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function applyRestaurantRelation(Category $category, Request $request): void
    {
        // Je récupère l'identifiant restaurant éventuel pour lier la catégorie
        $payload = $this->decodePayload($request);
        $restaurantId = $payload['restaurant_id'] ?? $payload['restaurant']['id'] ?? null;

        if ($restaurantId === null) {
            return;
        }

        $restaurant = $this->restaurantRepository->find($restaurantId);
        if (!$restaurant) {
            throw new NotFoundHttpException("Restaurant d'id {$restaurantId} introuvable pour la catégorie");
        }

        $category->setRestaurant($restaurant);
    }

    private function decodePayload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);

        return is_array($payload) ? $payload : [];
    }
}
