<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

// Je modélise un menu (ensemble de plats) proposé par le restaurant.
#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['menu:list', 'menu:detail'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Groups(['menu:list', 'menu:detail', 'menu:write'])]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 32)]
    #[Groups(['menu:list', 'menu:detail', 'menu:write'])]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['menu:detail', 'menu:write'])]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    #[Groups(['menu:list', 'menu:detail', 'menu:write'])]
    private string $price;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['menu:list', 'menu:detail'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['menu:detail'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'menus')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['menu:detail', 'menu:write'])]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToMany(targetEntity: MenuDish::class, mappedBy: 'menu', cascade: ['persist', 'remove'])]
    #[Groups(['menu:detail'])]
    private Collection $menuDishes;

    public function __construct()
    {
        $this->menuDishes = new ArrayCollection();
    }

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

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

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): self
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /** @return Collection<int, MenuDish> */
    public function getMenuDishes(): Collection
    {
        // Je fournis les associations menu/plat pour construire les cartes côté front.
        return $this->menuDishes;
    }

    public function addMenuDish(MenuDish $menuDish): self
    {
        if (!$this->menuDishes->contains($menuDish)) {
            $this->menuDishes->add($menuDish);
            $menuDish->setMenu($this);
        }

        return $this;
    }

    public function removeMenuDish(MenuDish $menuDish): self
    {
        if ($this->menuDishes->removeElement($menuDish)) {
            if ($menuDish->getMenu() === $this) {
                $menuDish->setMenu(null);
            }
        }

        return $this;
    }
}