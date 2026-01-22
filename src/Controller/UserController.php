<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer tous les utilisateurs
        $allUsers = $userRepository->findAll();

        // Filtrer pour enlever l'utilisateur connecté
        $users = array_filter($allUsers, function(User $user) {
            return $user !== $this->getUser();
        });

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_new' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $role = $form->get('role')->getData();
                    $user->setRoles([$role]);

                    $plainPassword = $form->get('password')->getData();
                    $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Utilisateur créé avec succès.');

                    return $this->redirectToRoute('app_user_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la création de l’utilisateur.');
                }
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs du formulaire.');
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user, ['is_new' => false]);

        $currentRole = in_array('ROLE_ADMIN', $user->getRoles()) ? 'ROLE_ADMIN' : 'ROLE_USER';
        $form->get('role')->setData($currentRole);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $role = $form->get('role')->getData();
                    $user->setRoles([$role]);

                    $plainPassword = $form->get('password')->getData();
                    if ($plainPassword) {
                        $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
                    }

                    $entityManager->flush();

                    $this->addFlash('success', 'Utilisateur modifié avec succès.');

                    return $this->redirectToRoute('app_user_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la modification de l’utilisateur.');
                }
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs.');
            }
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_user_index');
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression de l’utilisateur.');
        }

        return $this->redirectToRoute('app_user_index');
    }
}
