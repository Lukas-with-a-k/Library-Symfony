<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\RoomReservation;
use App\Repository\RoomRepository;
use App\Repository\RoomReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class RoomController extends AbstractController
{
    private RoomReservationRepository $reservationRepository;
    private LoggerInterface $logger;

    public function __construct(
        RoomReservationRepository $reservationRepository,
        private EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->logger = $logger;
    }

    #[Route('/api/rooms/availability/{id}', name: 'api_room_availability', methods: ['GET'])]
    public function getRoomAvailability(Room $room): JsonResponse
    {
        try {
            $reservations = $this->reservationRepository->findBy(['room' => $room]);

            $events = array_map(function (RoomReservation $reservation) {
                return [
                    'id' => $reservation->getId(),
                    'title' => 'Reserved',
                    'start' => $reservation->getStartDate()->format('Y-m-d\TH:i:s'),
                    'end' => $reservation->getEndDate()->format('Y-m-d\TH:i:s'),
                    'color' => '#ff5733',
                    'allDay' => false
                ];
            }, $reservations);

            return new JsonResponse($events);
        } catch (\Exception $e) {
            $this->logger->error('Error loading room availability: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('rooms/reserve', name: 'room_reserve', methods: ['POST'])]
    public function reserveRoom(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['roomId'], $data['startTime'], $data['endTime'])) {
            return new JsonResponse(['error' => 'Invalid request data.'], Response::HTTP_BAD_REQUEST);
        }
    
        try {
            $user = $this->getUser();

            if ($user->getSubscriptionType() === null) {
                return new JsonResponse(['error' => 'You need an active subscription to reserve a room.'], 403);
            }
    
            $existingReservation = $this->reservationRepository->findOneBy(['user' => $user]);
            if ($existingReservation) {
                return new JsonResponse(['error' => 'You already have an active reservation.'], 409);
            }
    
            $startTime = new \DateTime($data['startTime'], new \DateTimeZone('Europe/Paris'));
            $endTime = new \DateTime($data['endTime'], new \DateTimeZone('Europe/Paris'));

    
            if ($startTime >= $endTime) {
                return new JsonResponse(['error' => 'End time must be after start time.'], 400);
            }
    
            $room = $this->entityManager->getRepository(Room::class)->find($data['roomId']);
            if (!$room) {
                return new JsonResponse(['error' => 'Room not found.'], 404);
            }
    
            $conflicts = $this->reservationRepository->findConflictingReservations($room, $startTime, $endTime);
            if (!empty($conflicts)) {
                return new JsonResponse(['error' => 'This time slot is already reserved.'], 409);
            }
    
            $reservation = new RoomReservation();
            $reservation->setRoom($room)
                        ->setUser($user)
                        ->setStartDate($startTime)
                        ->setEndDate($endTime);
    
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
    
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Error reserving room: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }

    #[Route('rooms/cancel', name: 'room_cancel', methods: ['POST'])]
    public function cancelReservation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $reservationId = $data['reservationId'];
            $reservation = $this->reservationRepository->find($reservationId);

            if (!$reservation || $reservation->getUser() !== $this->getUser()) {
                return new JsonResponse(['error' => 'Reservation not found or not owned by user'], 403);
            }

            $this->entityManager->remove($reservation);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->logger->error('Error cancelling reservation: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }

    #[Route('/admin/rooms/cancel/{id}', name: 'admin_room_cancel', methods: ['POST'])]
public function cancelReservationAdmin(RoomReservation $reservation, EntityManagerInterface $entityManager): Response
{
    if (!$this->isGranted('ROLE_ADMIN')) {
        $this->addFlash('error', 'You do not have permission to perform this action.');
        return $this->redirectToRoute('admin_rooms', ['id' => $reservation->getRoom()->getId()]);
    }

    $roomId = $reservation->getRoom()->getId();
    $entityManager->remove($reservation);
    $entityManager->flush();

    $this->addFlash('success', 'Reservation successfully cancelled.');
    return $this->redirectToRoute('admin_rooms', ['id' => $roomId]);
}


    #[Route('rooms', name: 'room_index')]
public function index(RoomRepository $roomRepository, Request $request): Response
{
    $rooms = $roomRepository->findAll();

    return $this->render('rooms/index.html.twig', [
        'rooms' => $rooms, 
    ]);
}

    #[Route('/my-reservations', name: 'app_my_reservations')]
    public function myReservations(): Response
    {
        $user = $this->getUser();
        $reservations = $this->reservationRepository->findBy(['user' => $user]);

        return $this->render('rooms/my_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}
