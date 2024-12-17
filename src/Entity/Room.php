<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $capacity = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $equipment = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomReservation::class, cascade: ['remove'])]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getEquipment(): ?array
    {
        return $this->equipment;
    }

    public function setEquipment(?array $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    /**
     * @return Collection<int, RoomReservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(RoomReservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(RoomReservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }
}
