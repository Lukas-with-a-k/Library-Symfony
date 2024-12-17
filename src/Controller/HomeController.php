<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Book;
use App\Repository\BookRepository;

class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_home')]
    public function index(BookRepository $bookRepository): Response
    {
        $randomBook = $bookRepository->findRandomBook();
        $genres = $bookRepository->findAllGenres();

        return $this->render('home/index.html.twig', [
            'randomBook' => $randomBook,
            'genres' => $genres,
        ]);
    
    }
}
