<?php

namespace App\Entity;

use App\Repository\SiteInfosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteInfosRepository::class)]

// Indique à Doctrine que cette entité possède des méthodes qui seront
// appelées automatiquement lors des événements PrePersist et PreUpdate.
#[ORM\HasLifecycleCallbacks]
class SiteInfos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // L'identifiant doit être unique afin d'éviter d'avoir deux paramètres
    // portant le même nom (ex : deux "opening_hours").
    #[ORM\Column(length: 100, unique: true)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $value = null;

    // Date de création de l'enregistrement.
    // Renseignée automatiquement lors du premier persist.
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    // Date de la dernière modification.
    // Mise à jour automatiquement à chaque modification.
    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    // Cette méthode est appelée automatiquement par Doctrine
    // juste avant le premier INSERT en base de données.
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    // Cette méthode est appelée automatiquement par Doctrine
    // juste avant chaque UPDATE.
    // On ne modifie que la date de dernière mise à jour.
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}