<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Je conserve ce contrôleur par défaut pour vérifier rapidement que le routing Symfony fonctionne.
class DefaultController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        // Je renvoie une simple réponse textuelle pour servir de vérification rapide.
        return new Response('Hello World!');
    }
}