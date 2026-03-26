<?php

namespace App\Entity;

use App\Repository\ModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
class Model
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\Column(length: 50)]
    private string $site;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $baseModelId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $systemPrompt = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'external_id', nullable: true, onDelete: 'SET NULL')]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, AccessGrant>
     */
    #[ORM\OneToMany(targetEntity: AccessGrant::class, mappedBy: 'model', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $accessGrants;

    public function __construct(
        string $externalId,
        string $site,
        string $name,
        ?string $baseModelId = null,
        ?string $description = null,
        ?string $systemPrompt = null,
        bool $isActive = true,
        ?User $owner = null,
    ) {
        $this->externalId = $externalId;
        $this->site = $site;
        $this->name = $name;
        $this->baseModelId = $baseModelId;
        $this->description = $description;
        $this->systemPrompt = $systemPrompt;
        $this->isActive = $isActive;
        $this->owner = $owner;
        $this->updatedAt = new \DateTimeImmutable();
        $this->accessGrants = new ArrayCollection();
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBaseModelId(): ?string
    {
        return $this->baseModelId;
    }

    public function setBaseModelId(?string $baseModelId): void
    {
        $this->baseModelId = $baseModelId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(?string $systemPrompt): void
    {
        $this->systemPrompt = $systemPrompt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Collection<int, AccessGrant>
     */
    public function getAccessGrants(): Collection
    {
        return $this->accessGrants;
    }

    public function addAccessGrant(AccessGrant $accessGrant): void
    {
        if (!$this->accessGrants->contains($accessGrant)) {
            $this->accessGrants->add($accessGrant);
            $accessGrant->setModel($this);
        }
    }

    public function clearAccessGrants(): void
    {
        $this->accessGrants->clear();
    }
}
