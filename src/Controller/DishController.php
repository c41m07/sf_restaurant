<?php

//! TODO: enlever les méthodes GET sur POST/PUT/DELETE pour avoir des routes propres

namespace App\Controller;

use App\Entity\Dish;
use App\Repository\DishRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/dish', name: 'api_dish_')]
final class DishController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private DishRepository $repository,
        private RestaurantRepository $restaurantRepository,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        // Je crée un plat en dur tant que je n'ai pas branché la vraie requête
        $dish = new Dish();

        // Valeurs de test pour vérifier que l'enregistrement se passe bien
        $dish->setUuid('uuid-dish-1');
        $dish->setTitle('Plat signature de la maison');
        $dish->setDescription('Une description fictive pour vérifier le flux');
        $dish->setPrice('19.90');
        $dish->setCreatedAt(new \DateTime());
        $dish->setRestaurant($this->restaurantRepository->find(1));

        // J'ajoute l'entité dans le suivi Doctrine
        $this->manager->persist($dish);

        // Je sauvegarde tout de suite pour rester simple
        $this->manager->flush();

        // Je confirme la création au client
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
    public function edit(int $id): Response
    {
        // Je charge l'entité à modifier
        $dish = $this->repository->find($id);

        // Je garde la même gestion d'erreur qu'au dessus
        if (!$dish) {
            throw new NotFoundHttpException("Plat d'id {$id} introuvable");
        }

        // Exemple simple : je change juste le titre du plat
        $dish->setTitle('Nouveau titre du plat');
        $dish->setUpdatedAt(new \DateTime());

        // Doctrine suit déjà l'objet donc un flush suffit
        $this->manager->flush();

        // Je renvoie un message pour confirmer
        return $this->json([
            'message' => 'Plat mis à jour',
            'id' => $dish->getId(),
            'title' => $dish->getTitle(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function delete(int $id): Response
    {
        // Je vérifie que le plat existe avant de supprimer
        $dish = $this->repository->find($id);

        // Même logique : si rien trouvé je renvoie 404
        if (!$dish) {
            throw new NotFoundHttpException("Plat d'id {$id} introuvable");
        }

        // Je supprime l'entité puis j'envoie la requête en base
        $this->manager->remove($dish);
        $this->manager->flush();

        // Je renvoie une confirmation simple
        return $this->json(['message' => "Plat d'id {$id} supprimé avec succès"]);
    }
}