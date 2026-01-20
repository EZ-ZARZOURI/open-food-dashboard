<?php

namespace App\Entity;

use App\Repository\WidgetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WidgetRepository::class)]
class Widget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'widgets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filterType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filterValue = null;

    #[ORM\Column]
    private ?int $position = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFilterType(): ?string
    {
        return $this->filterType;
    }

    public function setFilterType(?string $filterType): static
    {
        $this->filterType = $filterType;

        return $this;
    }

    public function getFilterValue(): ?string
    {
        return $this->filterValue;
    }

    public function setFilterValue(?string $filterValue): static
    {
        $this->filterValue = $filterValue;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
