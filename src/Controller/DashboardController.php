<?php

namespace App\Controller;

use App\Repository\WidgetRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_home')]
    public function index(
        Request $request,
        WidgetRepository $widgetRepo,
        ProductService $productService
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $widgets = $widgetRepo->findBy(
            ['owner' => $user],
            ['position' => 'ASC']
        );

        $widgetsWithProducts = [];
        $perPage = 4;

        foreach ($widgets as $widget) {

            // page spécifique à chaque widget 
            $page = (int) $request->query->get(
                'page_' . $widget->getId(),
                1
            );

            // Tous les produits du widget
            $allProducts = $productService->getProductsByWidget(
                $widget->getFilterType(),
                $widget->getFilterValue()
            );

            //  Pagination locale
            $totalProducts = count($allProducts);
            $totalPages = (int) ceil($totalProducts / $perPage);
            $offset = ($page - 1) * $perPage;
            $productsPage = array_slice($allProducts, $offset, $perPage);

            $widgetsWithProducts[] = [
                'widget'        => $widget,
                'products'      => $productsPage,
                'currentPage'   => $page,
                'totalPages'    => $totalPages,
            ];
        }

        return $this->render('dashboard/index.html.twig', [
            'widgetsWithProducts' => $widgetsWithProducts,
        ]);
    }
}
