<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['restaurant.read', 'user.read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['restaurant.read', 'user.read'])]
    private ?string $email = null;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: Restaurant::class, cascade: ['persist', 'remove'])]
    private ?Restaurant $restaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        if ($this->restaurant === $restaurant) {
            return $this;
        }

        if ($this->restaurant && $this->restaurant->getOwner() === $this) {
            $this->restaurant->setOwner(null);
        }

        $this->restaurant = $restaurant;

        if ($restaurant && $restaurant->getOwner() !== $this) {
            $restaurant->setOwner($this);
        }

        return $this;
    }
}