<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Repository\DishRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

// Je gère ici les opérations CRUD exposées pour les plats (Dish).
#[Route('api/dish', name: 'api_dish_')]
final class DishController extends AbstractController
{
    // Je centralise les services nécessaires (Doctrine + Serializer) pour rester lisible et testable.
    public function __construct(
        private EntityManagerInterface $manager,
        private DishRepository         $repository,
        private RestaurantRepository   $restaurantRepository,
        private SerializerInterface    $serializer,
    )
    {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $dishes = $this->repository->findAll();
        $payload = $this->serializer->serialize($dishes, 'json', ['groups' => ['dish:list']]);

        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $dish = $this->serializer->deserialize(
            $request->getContent(),
            Dish::class,
            'json',
            ['groups' => ['dish:write']]
        );

        $dish->setUuid('TODO_UUID_A_INTEGRER_' . $dish->getTitle());
        $dish->setRestaurant($this->restaurantRepository->find($this->getUser()->getRestaurant()->getId()));
        $dish->setCreatedAt(new \DateTime());
        $this->applyRestaurantRelation($dish, $request);

        $this->manager->persist($dish);
        $this->manager->flush();

        return $this->json(
            ['message' => "plat créé avec succès {$dish->getId()} id"],
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        // Je récupère le plat demandé
        $dish = $this->repository->find($id);

        // Si je ne trouve rien, je renvoie une 404 claire
        if (!$dish) {
            throw new NotFoundHttpException("Plat d'id {$id} introuvable");
        }

        // Je renvoie les infos principales via le Serializer pour rester cohérent
        $payload = $this->serializer->serialize($dish, 'json', ['groups' => ['dish:detail']]);
        return new JsonResponse($payload, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $dish = $this->repository->find($id);

        if (!$dish) {
            throw new NotFoundHttpException("Plat d'id {$id} introuvable");
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Dish::class,
            'json',
            ['groups' => ['dish:write'], 'object_to_populate' => $dish]
        );

        $dish->setUpdatedAt(new \DateTime());
        $this->applyRestaurantRelation($dish, $request);

        $this->manager->flush();

        return $this->json([
            'message' => 'Plat mis à jour',
            'id' => $dish->getId(),
            'title' => $dish->getTitle(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function delete(int $id): JsonResponse
    {
        $dish = $this->repository->find($id);

        if (!$dish) {
            throw new NotFoundHttpException("Plat d'id {$id} introuvable");
        }

        $this->manager->remove($dish);
        $this->manager->flush();

        return $this->json(['message' => "Plat d'id {$id} supprimé avec succès"]);
    }

    private function applyRestaurantRelation(Dish $dish, Request $request): void
    {
        $payload = $this->decodePayload($request);
        $restaurantId = $payload['restaurant_id'] ?? $payload['restaurant']['id'] ?? null;

        if ($restaurantId === null) {
            return;
        }

        $restaurant = $this->restaurantRepository->find($restaurantId);
        if (!$restaurant) {
            throw new NotFoundHttpException("Restaurant d'id {$restaurantId} introuvable pour le plat");
        }

        $dish->setRestaurant($restaurant);
    }

    private function decodePayload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);

        return is_array($payload) ? $payload : [];
    }
}
