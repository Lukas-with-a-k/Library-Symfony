<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findBookByIsbn(string $isbn): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isbn = :isbn')
            ->setParameter('isbn', $isbn)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBookByTitle(string $title): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.title = :title')
            ->setParameter('title', $title)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBookByAuthor(string $author): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRandomBook(): ?Book
{
    $connection = $this->getEntityManager()->getConnection();
    $sql = 'SELECT * FROM book ORDER BY RAND() LIMIT 1';

    $result = $connection->executeQuery($sql)->fetchAssociative();

    if (!$result) {
        return null;
    }

    

    return $this->getEntityManager()->getRepository(Book::class)->find($result['id']);
}

public function findAllGenres(): array
{
    $qb = $this->createQueryBuilder('b')
    ->select('DISTINCT b.genres')
    ->where('b.genres IS NOT NULL')
    ->getQuery();

    $results = $qb->getResult();

    $flattenedGenres = [];
    foreach ($results as $result) {
        if (is_array($result['genres'])) {
            $flattenedGenres = array_merge($flattenedGenres, $result['genres']);
        } else {
            $flattenedGenres[] = $result['genres'];
        }
    }

    return array_unique($flattenedGenres);
}

public function findByGenre(string $genre): array
{
    return $this->createQueryBuilder('b')
        ->where('b.genres = :genre')
        ->setParameter('genre', $genre)
        ->getQuery()
        ->getResult();
}
public function findCompleteBooks(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.title IS NOT NULL')
            ->andWhere('b.author IS NOT NULL')
            ->andWhere('b.cover IS NOT NULL')
            ->andWhere('b.description IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function findBySearchQuery(string $query): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.title LIKE :query OR b.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();
    }
}