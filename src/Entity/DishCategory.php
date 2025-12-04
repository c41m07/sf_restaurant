<?php

namespace App\Entity;

use App\Repository\DishCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: DishCategoryRepository::class)]
class DishCategory
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Dish::class, inversedBy: 'dishCategories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['dish:detail', 'category:detail'])]
    private ?Dish $dish = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'dishCategories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['dish:detail', 'category:detail'])]
    private ?Category $category = null;

    public function getDish(): ?Dish
    {
        return $this->dish;
    }

    public function setDish($dish): self
    {
        $this->dish = $dish;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory($category): self
    {
        $this->category = $category;

        return $this;
    }
}