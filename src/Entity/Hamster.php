<?php

namespace App\Entity;

use App\Repository\HamsterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: HamsterRepository::class)]
class Hamster
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[Groups(['hamsterlist'])]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['hamsterlist'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['hamsterlist'])]
    #[ORM\Column]
    private ?int $hunger = null;

    #[Groups(['hamsterlist'])]
    #[ORM\Column]
    private ?int $age = null;

    #[Groups(['hamsterlist'])]
    #[ORM\Column(length: 1)]
    private ?string $genre = null;

    #[Groups(['hamsterlist'])]
    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'hamsters')]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getHunger(): ?int
    {
        return $this->hunger;
    }

    public function setHunger(int $hunger): static
    {
        $this->hunger = $hunger;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
