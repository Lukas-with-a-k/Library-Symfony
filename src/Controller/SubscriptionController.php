<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SubscriptionFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionController extends AbstractController
{
    #[Route('/subscription', name: 'app_subscription', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
    
        if (!$user) {
            $this->addFlash('error', 'You need to log in to access this page.');
            return $this->redirectToRoute('app_login');
        }
    
        $form = $this->createForm(SubscriptionFormType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Invalid password.');
                return $this->redirectToRoute('app_subscription');
            }
    
            $subscriptionType = $form->get('subscriptionType')->getData();
            $endDate = new \DateTime();
    
            if ($subscriptionType === 'monthly') {
                $endDate->modify('+1 month');
            } elseif ($subscriptionType === 'yearly') {
                $endDate->modify('+1 year');
            }
    
            $user->setSubscriptionType($subscriptionType);
            $user->setSubscriptionEndDate($endDate);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Subscription updated successfully.');
            return $this->redirectToRoute('app_subscription');
        }
    
        if ($request->isMethod('POST') && $request->request->has('cancel_subscription')) {
            $user->setSubscriptionType(null);
            $user->setSubscriptionEndDate(null);
            $entityManager->flush();
    
            $this->addFlash('success', 'Your subscription has been cancelled.');
            return $this->redirectToRoute('app_subscription');
        }
    
        return $this->render('subscription/index.html.twig', [
            'form' => $form->createView(),
            'subscriptionType' => $user->getSubscriptionType(),
            'subscriptionEndDate' => $user->getSubscriptionEndDate(),
        ]);
    }
    
}
