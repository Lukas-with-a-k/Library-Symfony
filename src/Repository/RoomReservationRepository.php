<?php

namespace App\Repository;

use App\Entity\Room;
use App\Entity\RoomReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoomReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomReservation::class);
    }

    public function findConflictingReservations(Room $room, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.room = :room')
            ->andWhere('r.endDate > :startTime')
            ->andWhere('r.startDate < :endTime')
            ->setParameter('room', $room)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->getQuery()
            ->getResult();
    }
}

