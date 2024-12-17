<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\User;
use App\Form\LoanFormType;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BookController extends AbstractController
{
    private BookRepository $bookRepository;

    public function __construct(
        BookRepository $bookRepository,
        private LoanRepository $loanRepository
        )
    {
        $this->bookRepository = $bookRepository;
    }

    #[Route('/books', name: 'app_books')]
    public function index(): Response
    {
        $books = $this->bookRepository->findCompleteBooks();

        return $this->render('books/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/navbar/genres', name: 'app_navbar_genres')]
    public function navbarGenres(): Response
    {
        $allGenres = $this->bookRepository->findAllGenres();

        $excludedGenres = ['Catalogs.', 'Juvenile poetry.', 'Poetry.', 'Juvenile fiction.', 'Early works to 1800.', 'Drama.', 'Translations into Italian.', 'Exhibitions.']; 
        $filteredGenres = array_filter($allGenres, function ($genre) use ($excludedGenres) {
            return !in_array($genre, $excludedGenres);
        });

        return $this->render('components/_navbar_genres.html.twig', [
            'genres' => $filteredGenres,
        ]);
    }

    #[Route('/books/genre/{genre}', name: 'app_books_by_genre')]
    public function booksByGenre(string $genre): Response
    {
        $books = $this->bookRepository->findByGenre($genre);

        if (empty($books)) {
            $this->addFlash('warning', "No books found for genre: $genre");
            return $this->redirectToRoute('app_books');
        }

        return $this->render('books/list.html.twig', [
            'books' => $books,
            'genre' => $genre,
        ]);
    }

#[Route('/books/{id}', name: 'app_book_show')]
public function show(
    Book $book,
    Request $request,
    EntityManagerInterface $entityManager
): Response {
    $comment = new Comment();
    $comment->setBook($book);

    $commentForm = $this->createForm(CommentFormType::class, $comment);
    $commentForm->handleRequest($request);

    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        if ($this->getUser()) {
            $comment->setUser($this->getUser());
        }
        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'Your comment has been added!');
        return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
    }

    $loan = $this->loanRepository->findOneBy(['book' => $book, 'user' => $this->getUser()]);
    $loanIsActive = false;
    $canExtend = false;

    if ($loan && $loan->getEndDate() > new \DateTime()) {
        $loanIsActive = true;

        if (!$loan->isExtended()) {
            $canExtend = true;
        }
    }

    $reserveForm = null;

    if ($book->isAvailable() && !$loanIsActive) {
        $loan = new Loan();
        $loan->setBook($book);
        $loan->setUser($this->getUser());
        $loan->setStartDate(new \DateTime());
        $loan->setEndDate((new \DateTime())->modify('+6 days'));

        $reserveForm = $this->createForm(LoanFormType::class, $loan)->createView();
    }

    return $this->render('books/show.html.twig', [
        'book' => $book,
        'commentForm' => $commentForm->createView(),
        'loan' => $loan,
        'reserveForm' => $reserveForm,
        'loanIsActive' => $loanIsActive,
        'canExtend' => $canExtend,
    ]);
}


}