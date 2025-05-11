<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ChangePasswordFormType;
Use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

final class ChangePasswordController extends AbstractController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordEncoder,
        private EntityManagerInterface $entityManager,
    ) {
    }

    // #[Route('/change/password', name: 'app_change_password')]
    // public function index(): Response
    // {
    //     return $this->render('change_password/index.html.twig');
    // }

    #[Route('/change/password', name: 'app_change_password')]
    public function changePassword(UserRepository $userRepository, Request $request, UserInterface $user): Response
    {
        $user = $userRepository->find($user->getId());
        $form = $this->createForm(ChangePasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $encodedPassword = $this->passwordEncoder->hashPassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
            $this->entityManager->flush();
            $this->addFlash('success', 'Password updated successfully');
            return $this->redirectToRoute('app_change_password');
        }

        return $this->render('change_password/index.html.twig', [
            'passwordUpdateForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}
