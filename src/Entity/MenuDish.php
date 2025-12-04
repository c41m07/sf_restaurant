<?php

namespace App\Entity;

use App\Repository\MenuDishRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MenuDishRepository::class)]
class MenuDish
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuDishes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['menu:detail'])]
    private ?Menu $menu = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Dish::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['menu:detail'])]
    private ?Dish $dish = null;

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }

    public function getDish(): ?Dish
    {
        return $this->dish;
    }

    public function setDish(?Dish $dish): self
    {
        $this->dish = $dish;

        return $this;
    }
}