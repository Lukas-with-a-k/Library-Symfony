<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Room;
use App\Entity\User;
use App\Form\RoomType;
use App\Form\BookFormType;
use App\Entity\RoomReservation;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/admin', name: 'admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private string $uploadDir;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->uploadDir = $params->get('upload_dir');
    }

    //utilisateurs
    #[Route('/users', name: 'admin_users')]
    public function manageUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/users/ban/{id}', name: 'users_ban')]
    public function banUser(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setIsBanned(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'User banned successfully.');
        return $this->redirectToRoute('admin_admin_users');
    }

    #[Route('/users/unban/{id}', name: 'users_unban')]
    public function unbanUser(User $user, EntityManagerInterface $entityManager): Response
    {
    $user->setIsBanned(false); 
    $entityManager->flush();

    $this->addFlash('success', 'User unbanned successfully.');
    return $this->redirectToRoute('admin_admin_users');
}

    #[Route('/users/role/{id}', name: 'users_role')]
    public function changeUserRole(User $user, Request $request): Response
    {
        $role = $request->query->get('role');
        $user->setRoles([$role]);
        $this->entityManager->flush();

        $this->addFlash('success', 'User role updated successfully.');
        return $this->redirectToRoute('admin_admin_users');
    }

    //livres
    #[Route('/books', name: 'books')]
    public function manageBooks(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll();
        return $this->render('admin/books.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/books/return/{id}', name: 'books_return')]
    public function markBookReturned(Book $book, LoanRepository $loanRepository, EntityManagerInterface $entityManager): Response
    {
        $loans = $loanRepository->findBy(['book' => $book]);

    foreach ($loans as $loan) {
        $entityManager->remove($loan);
    }

    $book->setIsAvailable(true);
    $book->setDateRestitutionPrevue(null);
    $entityManager->flush();

    $this->addFlash('success', 'Book marked as returned, and loans have been cleared.');
    return $this->redirectToRoute('admin_books');
    }

    #[Route('/books/delayed', name: 'books_delayed')]
    public function showDelayedBooks(BookRepository $bookRepository): Response
    {
        $today = new \DateTime();
        $delayedBooks = $bookRepository->findByDelayedReturns($today);

        return $this->render('admin/delayed_books.html.twig', [
            'books' => $delayedBooks,
        ]);
    }

    #[Route('/books/detail/{id}', name: 'books_detail')]
    public function bookDetail(Book $book, LoanRepository $loanRepository): Response
    {
        $loans = $loanRepository->findBy(['book' => $book]);
        return $this->render('admin/book_detail.html.twig', [
            'book' => $book,
            'loans' => $loans,
        ]);
    }

    #[Route('/books/add', name: 'books_add')]
    public function addBook(Request $request): Response
    {
        $book = new Book();

        $form = $this->createForm(BookFormType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $genresInput = $form->get('genres')->getData();
            $book->setGenres(array_map('trim', explode(',', $genresInput)));

            $coverFile = $form->get('coverFile')->getData();
            dump($coverFile);
            if ($coverFile) {
                $newFilename = uniqid() . '.' . $coverFile->guessExtension();

                try {
                    $coverFile->move($this->uploadDir, $newFilename);
                    $book->setCover('/uploads/' . $newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de la couverture.');
                    return $this->redirectToRoute('books_add');
                }
            }

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            $this->addFlash('success', 'Livre ajouté avec succès.');
            return $this->redirectToRoute('admin_books');
        }

        return $this->render('admin/book_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un Livre',
        ]);
    }

    

#[Route('/books/edit/{id}', name: 'books_edit')]
public function editBook(Book $book, Request $request, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(BookFormType::class, $book);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $genresInput = $form->get('genres')->getData();
        $book->setGenres(array_map('trim', explode(',', $genresInput)));
    
        $entityManager->flush();
    
        $this->addFlash('success', 'Livre modifié avec succès.');
        return $this->redirectToRoute('admin_books');
    }

    return $this->render('admin/book_form.html.twig', [
        'form' => $form->createView(),
        'title' => 'Modifier le Livre : ' . $book->getTitle(),
    ]);
}


    #[Route('/books/delete/{id}', name: 'books_delete')]
    public function deleteBook(Book $book): Response
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();

        $this->addFlash('success', 'Book deleted successfully.');
        return $this->redirectToRoute('admin_books');
    }

    // salles
    #[Route('/rooms', name: 'rooms')]
    public function manageRooms(RoomRepository $roomRepository): Response
    {
        $rooms = $roomRepository->findAll();
        return $this->render('admin/rooms.html.twig', [
            'rooms' => $rooms,
        ]);
    }

    #[Route('/rooms/detail/{id}', name: 'rooms_detail')]
    public function roomDetail(Room $room): Response
    {
        $reservations = $room->getReservations();
        return $this->render('admin/room_detail.html.twig', [
            'room' => $room,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/rooms/add', name: 'rooms_add')]
    public function addRoom(Request $request): Response
    {
        $room = new Room(); 

        $form = $this->createForm(RoomType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($room);
            $this->entityManager->flush();

            $this->addFlash('success', 'Room added successfully.');
            return $this->redirectToRoute('admin_rooms');
        }

        return $this->render('admin/room_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Add Room',  

        ]);
    }

    #[Route('/rooms/edit/{id}', name: 'rooms_edit')]
    public function editRoom(Room $room, Request $request): Response
    {
        $roomForm = $this->createForm(RoomType::class, $room);
        $roomForm->handleRequest($request);

        if ($roomForm->isSubmitted() && $roomForm->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Room updated successfully.');
            return $this->redirectToRoute('admin_rooms');
        }

        return $this->render('admin/room_form.html.twig', [
            'form' => $roomForm->createView(),
            'title' => 'Edit Room',
        ]);
    }

    #[Route('/rooms/delete/{id}', name: 'rooms_delete')]
    public function deleteRoom(Room $room): Response
    {
        $this->entityManager->remove($room);
        $this->entityManager->flush();

        $this->addFlash('success', 'Room deleted successfully.');
        return $this->redirectToRoute('admin_rooms');
    }
}
