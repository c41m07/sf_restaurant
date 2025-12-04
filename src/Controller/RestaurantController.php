<?php


namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('api/restaurant', name: 'api_restaurant_')]
final class RestaurantController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private RestaurantRepository   $repository,
        private UserRepository         $userRepository,
        private SerializerInterface    $serializer,
        private UrlGeneratorInterface  $urlGenerator,
    )
    {
    }


    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $restaurants = $this->repository->findAll();
        $data = $this->serializer->serialize($restaurants, 'json', ['groups' => ['restaurant:list']]);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/add', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize(
            $request->getContent(),
            Restaurant::class,
            'json',
            ['groups' => ['restaurant:write']]
        );
        $restaurant->setCreatedAt(new \DateTime());
        $restaurant->setOwner($this->userRepository->find(1)); //TODO remplacer par user connecté

        // J'ajoute l'entité dans le suivi Doctrine
        $this->manager->persist($restaurant);

        // Je sauvegarde tout de suite pour rester simple
        $this->manager->flush();

        // Je confirme la création au client
        return $this->json(
            ['message' => "restaurant créé avec succée {$restaurant->getId()} id"],
            status: Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id): JsonResponse
    {
        // Je récupère le restaurant demandé
        $restaurant = $this->repository->findOneBy(['id' => $id]);

        // Si je trouve, je renvoie une 200 claire
        if ($restaurant) {
            $responsedata = $this->serializer->serialize($restaurant, 'json', ['groups' => ['restaurant:detail']]);
            return new JsonResponse($responsedata, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(['message' => 'Restaurant introuvable'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'], requirements: ['id' => '\\d+'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        // Je charge l'entité à modifier
        $restaurant = $this->repository->find($id);

        // Je garde la même gestion d'erreur qu'au dessus
        if (!$restaurant) {
            throw new NotFoundHttpException("Restaurant d'id {$id} introuvable");
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Restaurant::class,
            'json',
            ['groups' => ['restaurant:write'], 'object_to_populate' => $restaurant]
        );

        $this->manager->persist($restaurant);
        // Doctrine suit déjà l'objet donc un flush suffit
        $this->manager->flush();

        // Je renvoie un message pour confirmer
        return $this->json(['message' => 'Restaurant d\'id ' . $id . ' modifié avec succès']);

    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function delete(int $id): JsonResponse
    {
        // Je vérifie que le restaurant existe avant de supprimer
        $restaurant = $this->repository->find($id);

        // Même logique : si rien trouvé je renvoie 404
        if (!$restaurant) {
            throw new NotFoundHttpException("Restaurant d'id {$id} introuvable");
        }

        // Je supprime l'entité puis j'envoie la requête en base
        $this->manager->remove($restaurant);
        $this->manager->flush();

        // Je renvoie une confirmation simple
        return $this->json(['message' => 'Restaurant d\'id ' . $id . ' supprimé avec succès']);
    }

}