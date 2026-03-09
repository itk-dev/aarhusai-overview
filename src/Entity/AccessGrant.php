<?php

namespace App\Entity;

use App\Repository\AccessGrantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccessGrantRepository::class)]
class AccessGrant
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $externalId;

    #[ORM\ManyToOne(targetEntity: Model::class, inversedBy: 'accessGrants')]
    #[ORM\JoinColumn(name: 'model_external_id', referencedColumnName: 'external_id', nullable: false)]
    private Model $model;

    #[ORM\Column(length: 50)]
    private string $resourceType;

    #[ORM\Column(length: 255)]
    private string $resourceId;

    #[ORM\Column(length: 50)]
    private string $principalType;

    #[ORM\Column(length: 255)]
    private string $principalId;

    #[ORM\Column(length: 50)]
    private string $permission;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct(
        string $externalId,
        Model $model,
        string $resourceType,
        string $resourceId,
        string $principalType,
        string $principalId,
        string $permission,
    ) {
        $this->externalId = $externalId;
        $this->model = $model;
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
        $this->principalType = $principalType;
        $this->principalId = $principalId;
        $this->permission = $permission;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getPrincipalType(): string
    {
        return $this->principalType;
    }

    public function getPrincipalId(): string
    {
        return $this->principalId;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
