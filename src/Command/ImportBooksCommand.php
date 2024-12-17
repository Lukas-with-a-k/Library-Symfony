<?php

namespace App\Command;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'import:books',
    description: 'Imports books into the database.'
)]
class ImportBooksCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private HttpClientInterface $httpClient;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        HttpClientInterface $httpClient
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->httpClient = $httpClient;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = 'fiction'; 
        $maxResults = 40; 
        $totalBooksImported = 0;

        $output->writeln('<info>Starting import from Google Books API...</info>');

        for ($startIndex = 0; $startIndex < 200; $startIndex += $maxResults) {
            $url = sprintf(
                'https://www.googleapis.com/books/v1/volumes?q=%s&startIndex=%d&maxResults=%d',
                urlencode($query),
                $startIndex,
                $maxResults
            );

            try {
                $response = $this->httpClient->request('GET', $url);
                $data = $response->toArray();
            } catch (\Exception $e) {
                $output->writeln('<error>Failed to fetch data from Google Books API: ' . $e->getMessage() . '</error>');
                break;
            }

            if (!isset($data['items']) || count($data['items']) === 0) {
                $output->writeln('<comment>No more books found. Stopping import.</comment>');
                break;
            }

            foreach ($data['items'] as $item) {
                if (!isset($item['volumeInfo'])) {
                    continue;
                }

                $volumeInfo = $item['volumeInfo'];
                $isbn = $this->extractIsbn($volumeInfo);

                if (!$isbn) {
                    $output->writeln('<comment>Skipping book without valid ISBN.</comment>');
                    continue;
                }

                $existingBook = $this->entityManager->getRepository(Book::class)->findOneBy(['isbn' => $isbn]);
                if ($existingBook) {
                    $output->writeln('<comment>Skipping duplicate: ' . ($volumeInfo['title'] ?? 'Unknown Title') . '</comment>');
                    continue;
                }

                $book = new Book();
                $book->setTitle($volumeInfo['title'] ?? 'Unknown Title');
                $book->setAuthor($volumeInfo['authors'][0] ?? 'Unknown Author');
                $book->setIsbn($isbn);
                $book->setDescription($volumeInfo['description'] ?? 'No description available');
                $book->setRating(rand(1, 5));
                $book->setGenres($volumeInfo['categories'] ?? ['General']);
                $book->setIsAvailable([true, false][array_rand([true, false])]);
                $book->setState(['good', 'fair', 'poor', 'new'][array_rand(['good', 'fair', 'poor', 'new'])]);

                $thumbnail = $volumeInfo['imageLinks']['thumbnail'] ?? null;
                if ($thumbnail && strpos($thumbnail, '1x1') === false) {
                    $book->setCover($thumbnail);
                } else {
                    $output->writeln('<comment>Skipping book with invalid or missing cover: ' . $book->getTitle() . '</comment>');
                    continue;
                }

                $errors = $this->validator->validate($book);
                if (count($errors) > 0) {
                    $output->writeln('<error>Invalid book skipped: ' . (string) $errors . '</error>');
                    continue;
                }

                $this->entityManager->persist($book);
                $totalBooksImported++;
                $output->writeln('<info>Imported: ' . $book->getTitle() . '</info>');
            }

            $this->entityManager->flush();
        }

        $output->writeln("<info>Import completed. Total books imported: {$totalBooksImported}</info>");
        return Command::SUCCESS;
    }

    private function extractIsbn(array $volumeInfo): ?string
    {
        if (!isset($volumeInfo['industryIdentifiers'])) {
            return null;
        }

        foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
            if ($identifier['type'] === 'ISBN_13') {
                return $identifier['identifier'];
            }
        }

        return null;
    }
}
