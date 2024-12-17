<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'account_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Your account has been updated.');

            return $this->redirectToRoute('account_index');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/my-loans', name: 'app_my_loans')]
    public function myLoans(LoanRepository $loanRepository): Response
{
    $user = $this->getUser();

    if (!$user) {
        throw $this->createAccessDeniedException('You need to log in to see your loans.');
    }

    $loans = $loanRepository->findBy(['user' => $user]);

    return $this->render('account/my_loans.html.twig', [
        'loans' => $loans,
    ]);
}
}
