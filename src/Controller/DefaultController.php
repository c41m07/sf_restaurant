<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Je conserve ce contrôleur par défaut pour vérifier rapidement que le routing Symfony fonctionne.
class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    #[OA\Get(summary: 'Endpoint de test du routing Symfony', tags: ['health'])]
    public function home(): Response
    {
        // Je renvoie une simple réponse textuelle pour servir de vérification rapide.
        return new Response('Hello World!');
    }
}