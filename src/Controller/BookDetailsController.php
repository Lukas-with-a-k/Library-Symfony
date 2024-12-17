<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BookDetailsController extends AbstractController
{
    private $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }
    #[Route('/book/details', name: 'app_book_details')]
    public function index(): Response
    {    
        $books = $this->bookRepository->findAll();

        $book = new Book();
        $book->setTitle('Le titre du livre');
        $book->setAuthor('Auteur');
        $book->setIsbn('123-456-7890');
        $book->setDescription('Description du livre');
        $book->setState('Disponible');
        $book->setTags(['tag1', 'tag2', 'tag3']);
        $book->setCover('https://example.com/cover.jpg');
        $book->setRating(4.5);
        $book->setAvailability(true);
        $book->setGenres($genres);
        $book->setSubjects($subjects);

        return $this->render('book_details/index.html.twig', [
            'books' => $books,
        ]);
    }
}
