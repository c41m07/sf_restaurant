<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

// Je représente ici la table des catégories, chaque objet correspond à une catégorie de menu.
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:list', 'category:detail', 'dish:detail'])]
    private ?int $id = null;

    // Je stocke un identifiant fonctionnel (UUID) pour faciliter les échanges avec le front.
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Groups(['category:list', 'category:detail', 'category:write', 'dish:detail'])]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 32)]
    #[Groups(['category:list', 'category:detail', 'category:write', 'dish:detail'])]
    private string $title;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['category:list', 'category:detail'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['category:detail'])]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'categories')]
    #[Groups(['category:detail', 'category:write'])]
    private ?Restaurant $restaurant = null;

    #[ORM\OneToMany(targetEntity: DishCategory::class, mappedBy: 'category', cascade: ['persist', 'remove'])]
    #[Groups(['category:detail'])]
    private Collection $dishCategories;

    public function __construct()
    {
        $this->dishCategories = new ArrayCollection();
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

    /** @return Collection<int, DishCategory> */
    public function getDishCategories(): Collection
    {
        // Je retourne ici la collection de jointures vers les plats liés à cette catégorie.
        return $this->dishCategories;
    }

    public function addDishCategory(DishCategory $dishCategory): self
    {
        if (!$this->dishCategories->contains($dishCategory)) {
            $this->dishCategories->add($dishCategory);
            $dishCategory->setCategory($this);
        }

        return $this;
    }

    public function removeDishCategory(DishCategory $dishCategory): self
    {
        if ($this->dishCategories->removeElement($dishCategory)) {
            if ($dishCategory->getCategory() === $this) {
                $dishCategory->setCategory(null);
            }
        }

        return $this;
    }
}