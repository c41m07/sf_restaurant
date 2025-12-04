<?php

//! TODO: enlever les méthodes GET sur POST/PUT/DELETE pour avoir des routes propres

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/category', name: 'api_category_')]
final class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CategoryRepository $repository,
        private RestaurantRepository $restaurantRepository,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
    public function new(): Response
    {
        // Je crée une catégorie en dur tant que je n'ai pas branché la vraie requête
        $category = new Category();

        // Valeurs de test pour vérifier que l'enregistrement se passe bien
        $category->setUuid('uuid-category-1');
        $category->setTitle('Catégorie phare du restaurant');
        $category->setCreatedAt(new \DateTime());
        $category->setRestaurant($this->restaurantRepository->find(1));

        // J'ajoute l'entité dans le suivi Doctrine
        $this->manager->persist($category);

        // Je sauvegarde tout de suite pour rester simple
        $this->manager->flush();

        // Je confirme la création au client
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
    public function edit(int $id): Response
    {
        // Je charge l'entité à modifier
        $category = $this->repository->find($id);

        // Je garde la même gestion d'erreur qu'au dessus
        if (!$category) {
            throw new NotFoundHttpException("Catégorie d'id {$id} introuvable");
        }

        // Exemple simple : je change juste le titre
        $category->setTitle('Nouveau titre de catégorie');
        $category->setUpdatedAt(new \DateTime());

        // Doctrine suit déjà l'objet donc un flush suffit
        $this->manager->flush();

        // Je renvoie un message pour confirmer
        return $this->json([
            'message' => 'Catégorie mise à jour',
            'id' => $category->getId(),
            'title' => $category->getTitle(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function delete(int $id): Response
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
}