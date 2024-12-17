<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Form\LoanFormType;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/loan')]
class LoanController extends AbstractController
{
    #[Route('/reserve/{id}', name: 'loan_reserve', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function reserveBook(
        Book $book,
        Request $request,
        EntityManagerInterface $entityManager,
        LoanRepository $loanRepository
    ): Response {
        if (!$this->getUser()) {
            $this->addFlash('error', 'You need to log in to reserve a book.');
            return $this->redirectToRoute('app_login');
        }

        if (!$book->isAvailable()) {
            $this->addFlash('warning', 'This book is not available for reservation.');
            return $this->redirectToRoute('app_books');
        }

        $existingLoan = $loanRepository->findOneBy([
            'book' => $book,
            'user' => $this->getUser(),
        ]);

        if ($existingLoan) {
            $this->addFlash('warning', 'You have already reserved this book.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        $loan = new Loan();
        $loan->setBook($book);
        $loan->setUser($this->getUser());
        $loan->setStartDate(new \DateTime());
        $loan->setEndDate((new \DateTime())->modify('+6 days'));
        $book->setDateRestitutionPrevue($loan->getEndDate());



        $form = $this->createForm(LoanFormType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setIsAvailable(false);

            $entityManager->persist($loan);
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', 'Book reserved successfully!');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('loan/reserve.html.twig', [
            'form' => $form->createView(),
            'book' => $book,
        ]);
    }

    #[Route('/loans/extend/{id}', name: 'extend_loan')]
    public function extendLoan(Loan $loan, EntityManagerInterface $entityManager): Response
    {
        if ($loan->isExtended()) {
            $this->addFlash('warning', 'This loan has already been extended.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }
    
        $loan->extendLoan();
    
        $book = $loan->getBook();
        $book->setDateRestitutionPrevue($loan->getEndDate());
    
        $entityManager->flush();
    
        $this->addFlash('success', 'Loan extended successfully!');
        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }
    
}

