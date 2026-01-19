<?php

// src/Controller/OpenFoodController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Application\Product\GetProductsUseCase;

class OpenFoodController extends AbstractController
{
    public function __construct(private GetProductsUseCase $getProductsUseCase) {}

    #[Route('/', name: 'open_food_home')]
    public function index(Request $request): Response
    {
        $page = (int) $request->query->get('page', 1);
        $pageSize = 12;

        // Récupère les produits
        $products = $this->getProductsUseCase->execute($page, $pageSize);

        // Transforme les objets Product en tableaux légers
        $productsArray = array_map(fn($p) => [
            'name' => $p->name,
            'brand' => $p->brand,
            'code' => $p->code,
            'countries' => $p->countries,
            'categories' => $p->categories,
            'imageUrl' => $p->imageUrl,
        ], $products);

        // Rendu Twig sans totalPages
        return $this->render('open_food/index.html.twig', [
            'products' => $productsArray,
            'currentPage' => $page,
            'pageSize' => $pageSize,
        ]);
    }
}
