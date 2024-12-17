<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(length: 13, nullable: false)]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publicationDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $rating = null;

    #[ORM\Column(nullable: true)]
    private ?array $tags = null;

    #[ORM\Column(length: 55)]
    private ?string $state = null;

    #[Assert\NotBlank(message: 'Cover is required.')]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover = null;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $subjects = null;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $genres = null;
 
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Comment::class, cascade: ['remove'])]
    private Collection $comments;

    #[ORM\Column(type: 'boolean')]
    private bool $isAvailable = true;
    
    /**
     * @var Collection<int, Loan>
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Loan::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $loans;


    public function __construct()
    {
    $this->comments = new ArrayCollection();
    $this->loans = new ArrayCollection();
    
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
private ?\DateTimeInterface $dateRestitutionPrevue = null;

public function getDateRestitutionPrevue(): ?\DateTimeInterface
{
    return $this->dateRestitutionPrevue;
}

public function setDateRestitutionPrevue(?\DateTimeInterface $dateRestitutionPrevue): self
{
    $this->dateRestitutionPrevue = $dateRestitutionPrevue;

    return $this;
}


    public function getComments(): Collection
    {
    return $this->comments;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTimeInterface $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }
    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(?string $cover): self
    {
        $this->cover = $cover;
        return $this;
    }

    public function getSubjects(): ?array
    {
        return $this->subjects;
    }

    public function setSubjects(?array $subjects): self
    {
        $this->subjects = $subjects;
        return $this;
    }

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): self
    {
        $this->genres = $genres;
        return $this;
    }

    /**
     * @return Collection<int, Loan>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): static
    {
        if (!$this->loans->contains($loan)) {
            $this->loans->add($loan);
            $loan->setBook($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): static
    {
        if ($this->loans->removeElement($loan)) {
            if ($loan->getBook() === $this) {
                $loan->setBook(null);
            }
        }

        return $this;
    }

    public function isAvailable(): bool
    {
    return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
    $this->isAvailable = $isAvailable;

    return $this;
    }
    public function updateDateRestitutionPrevue(): void
    {
        if ($this->loans->count() > 0) {
            $latestLoan = $this->loans->last(); 
            $this->setDateRestitutionPrevue($latestLoan->getEndDate());
        }
    }
    
}
