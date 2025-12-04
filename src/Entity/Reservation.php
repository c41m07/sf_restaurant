<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

// Je stocke ici les réservations des clients (date, convives, contact).
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:list', 'reservation:detail'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Groups(['reservation:list', 'reservation:detail', 'reservation:write', 'restaurant:detail'])]
    private string $uuid;

    #[ORM\Column(type: 'smallint')]
    #[Groups(['reservation:list', 'reservation:detail', 'reservation:write'])]
    private int $guestNumber;

    #[ORM\Column(type: 'date')]
    #[Groups(['reservation:list', 'reservation:detail', 'reservation:write'])]
    private \DateTimeInterface $reservationDate;

    #[ORM\Column(type: 'time')]
    #[Groups(['reservation:detail', 'reservation:write'])]
    private \DateTimeInterface $reservationTime;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['reservation:detail', 'reservation:write'])]
    private ?string $allergyNote = null;

    #[ORM\Column(type: 'string', length: 16)]
    #[Groups(['reservation:list', 'reservation:detail', 'reservation:write'])]
    private string $status;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['reservation:list', 'reservation:detail'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['reservation:detail'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:detail', 'reservation:write', 'user:read'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['reservation:detail', 'reservation:write'])]
    private ?Restaurant $restaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getGuestNumber(): int
    {
        return $this->guestNumber;
    }

    public function setGuestNumber(int $guestNumber): self
    {
        $this->guestNumber = $guestNumber;

        return $this;
    }

    public function getReservationDate(): \DateTimeInterface
    {
        return $this->reservationDate;
    }

    public function setReservationDate(\DateTimeInterface $reservationDate): self
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    public function getReservationTime(): \DateTimeInterface
    {
        return $this->reservationTime;
    }

    public function setReservationTime(\DateTimeInterface $reservationTime): self
    {
        $this->reservationTime = $reservationTime;

        return $this;
    }

    public function getAllergyNote(): ?string
    {
        return $this->allergyNote;
    }

    public function setAllergyNote(?string $allergyNote): self
    {
        $this->allergyNote = $allergyNote;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}