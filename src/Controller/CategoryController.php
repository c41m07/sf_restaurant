<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(): JsonResponse
    {
        // Je récupère toutes les catégories puis les sérialise avec le groupe list
        $categories = $this->repository->findAll();
        $payload = $this->serializer->serialize($categories, 'json', ['groups' => ['category:list']]);

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
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
        return $this->json(['message' => "Catégorie d'id {$id} supprimée avec succès"]);
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
