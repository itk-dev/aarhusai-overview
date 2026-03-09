<?php

namespace App\Entity;

use App\Repository\ModelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
class Model
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ownedBy = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $externalId, string $name, ?string $ownedBy = null)
    {
        $this->externalId = $externalId;
        $this->name = $name;
        $this->ownedBy = $ownedBy;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getOwnedBy(): ?string
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?string $ownedBy): void
    {
        $this->ownedBy = $ownedBy;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
