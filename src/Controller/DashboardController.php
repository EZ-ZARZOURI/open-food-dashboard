<?php

namespace App\Controller;

use App\Repository\WidgetRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;

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

    #[Route('/dashboard/add-widget', name: 'dashboard_add_widget', methods: ['POST'])]
    public function addWidget(
        Request $request,
        WidgetRepository $widgetRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $filterType = $request->request->get('filterType');
        $filterValue = $request->request->get('filterValue');

        if ($filterType && $filterValue) {
            $widget = new Widget();
            $widget->setOwner($user)
                ->setType('products')
                ->setFilterType($filterType)
                ->setFilterValue($filterValue);

            // Calculer position automatique
            $lastWidget = $widgetRepo->findOneBy(['owner' => $user], ['position' => 'DESC']);
            $position = $lastWidget ? $lastWidget->getPosition() + 1 : 1;
            $widget->setPosition($position);

            $em->persist($widget);
            $em->flush();

            $this->addFlash('success', 'Widget ajouté avec succès !');
        } else {
            $this->addFlash('error', 'Impossible d’ajouter le widget. Veuillez remplir tous les champs.');
        }

        return $this->redirectToRoute('dashboard_home');
    }

    #[Route('/dashboard/edit-widget', name: 'dashboard_edit_widget', methods: ['POST'])]
    public function editWidget(
        Request $request,
        WidgetRepository $widgetRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non authentifié.');
            return $this->redirectToRoute('dashboard_home');
        }

        $id = $request->request->get('id');
        $filterType = $request->request->get('filterType');
        $filterValue = $request->request->get('filterValue');

        if (!$id || !$filterType || !$filterValue) {
            $this->addFlash('error', 'Champs manquants pour la modification.');
            return $this->redirectToRoute('dashboard_home');
        }

        $widget = $widgetRepo->find($id);

        if (!$widget || $widget->getOwner() !== $user) {
            $this->addFlash('error', 'Widget introuvable ou accès refusé.');
            return $this->redirectToRoute('dashboard_home');
        }

        try {
            $widget->setFilterType($filterType)
                ->setFilterValue($filterValue);

            $em->flush();

            $this->addFlash('success', 'Widget modifié avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la modification du widget.');
        }

        return $this->redirectToRoute('dashboard_home');
    }

    #[Route('/dashboard/delete-widget/{id}', name: 'dashboard_delete_widget', methods: ['POST'])]
    public function deleteWidget(
        Widget $widget,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user || $widget->getOwner() !== $user) {
            $this->addFlash('error', 'Suppression non autorisée.');
            return $this->redirectToRoute('dashboard_home');
        }

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_widget_' . $widget->getId(), $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('dashboard_home');
        }

        try {
            $em->remove($widget);
            $em->flush();

            $this->addFlash('success', 'Widget supprimé avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression du widget.');
        }

        return $this->redirectToRoute('dashboard_home');
    }


}
