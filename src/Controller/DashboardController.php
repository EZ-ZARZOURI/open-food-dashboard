<?php

namespace App\Controller;

use App\Repository\WidgetRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_home')]
    public function index(
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

        foreach ($widgets as $widget) {
            $products = $productService->getProducts(
                null,
                null,
                $widget->getPage(),
                $widget->getPageSize()
            );

            $widgetsWithProducts[] = [
                'widget'   => $widget,
                'products' => $products,
            ];
        }

        return $this->render('dashboard/index.html.twig', [
            'widgetsWithProducts' => $widgetsWithProducts,
        ]);
    }
}
