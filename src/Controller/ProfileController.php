<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\EditFormType;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;


final class ProfileController extends AbstractController
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer
    ) {
    }

    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();
        $editProfileForm = $this->createForm(EditFormType::class, $user);
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'editProfileForm' => $editProfileForm->createView()

        ]);
    }

    #[Route('/profile/edit/{id}', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        // Security check - ensure user can only edit their own profile
        $currentUser = $this->getUser();
        if ($currentUser !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own profile.');
        }
    
        
        // Store original email for comparison
        $originalEmail = $user->getEmail();
        

        $form = $this->createForm(EditFormType::class, $user);
        $form->handleRequest($request);


        
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email was changed
            if ($user->getEmail() !== $originalEmail) {
                // Mark user as unverified
                $user->setIsVerified(false);

                // Generate verification email
                $signatureComponents = $this->verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $user->getId(),
                    $user->getEmail()
                );

                // Send verification email
                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@example.com', 'Blog'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'signedUrl' => $signatureComponents->getSignedUrl(),
                        'expiresAtMessageKey' => $signatureComponents->getExpirationMessageKey(),
                        'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
                    ]);

                $this->mailer->send($email);

                $this->addFlash('warning', 'A verification email has been sent. Please check your inbox to verify your new email address.');
            }
           

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully');
            return $this->redirectToRoute('app_profile_edit', ['id' => $user->getId()]);
            
        }   

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'editProfileForm' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete/{id}', name: 'app_profile_delete', methods: ['POST'])]
    public function delete(int $id, SessionInterface $session, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage):Response
    {
        
        $currentUser = $tokenStorage->getToken()->getUser();
        
        // Check if the current user exists and matches the ID
        if (!$currentUser || $currentUser->getId() !== $id) {
            throw $this->createAccessDeniedException('You can only delete your own profile.');
        }

        // Remove user from database FIRST
        $entityManager->remove($currentUser);
        $entityManager->flush();

        // Clear security context
        $tokenStorage->setToken(null);  // Remove authentication
        $session->invalidate();         // Destroy session

        // Add flash message for confirmation
        $this->addFlash('success', 'Your account has been permanently deleted.');

 
        return $this->redirectToRoute('app_login');

    }
}
