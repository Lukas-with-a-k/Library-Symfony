<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * Récupère toutes les salles avec une capacité minimale donnée.
     *
     * @param int $minCapacity
     * @return Room[]
     */
    public function findByMinCapacity(int $minCapacity): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.capacity >= :minCapacity')
            ->setParameter('minCapacity', $minCapacity)
            ->orderBy('r.capacity', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les salles contenant un équipement spécifique.
     *
     * @param string $equipment
     * @return Room[]
     */
    public function findByEquipment(string $equipment): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('JSON_CONTAINS(r.equipment, :equipment) = 1')
            ->setParameter('equipment', json_encode($equipment))
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère une salle par son nom (insensible à la casse).
     *
     * @param string $name
     * @return Room|null
     */
    public function findOneByName(string $name): ?Room
    {
        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.name) = :name')
            ->setParameter('name', strtolower($name))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
