<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
// Je représente un utilisateur de l’application (client ou admin) avec ses rôles et son token API.
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['restaurant:detail', 'user:read', 'user:write', 'reservation:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['restaurant:detail', 'user:read', 'user:write', 'reservation:detail'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['restaurant:detail', 'user:read', 'user:write', 'reservation:detail'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $apiToken = null;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: Restaurant::class, cascade: ['persist', 'remove'])]
    #[Groups(['user:read'])]
    private ?Restaurant $restaurant = null;

    #[ORM\Column(length: 255)]
    #[Groups(['restaurant:detail', 'user:read', 'user:write', 'reservation:detail'])]
    #[SerializedName('first_name')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['restaurant:detail', 'user:read', 'user:write', 'reservation:detail'])]
    #[SerializedName('last_name')]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    #[ORM\Column(nullable: true)]
    private ?int $guestNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $allergy = null;

    /**@throws \Exception */
    public function __construct()
    {
        $this->apiToken = bin2hex(random_bytes(20));
    }

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): static
    {
        $this->apiToken = $apiToken;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getGuestNumber(): ?int
    {
        return $this->guestNumber;
    }

    public function setGuestNumber(?int $guestNumber): static
    {
        $this->guestNumber = $guestNumber;

        return $this;
    }

    public function getAllergy(): ?string
    {
        return $this->allergy;
    }

    public function setAllergy(?string $allergy): static
    {
        $this->allergy = $allergy;

        return $this;
    }
}