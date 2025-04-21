<?php

namespace App\Entity;

use App\Repository\PieceJointeAvisMembreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeAvisMembreRepository::class)]
class PieceJointeAvisMembre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointeAvisMembres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PieceJointe $piece = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointeAvisMembres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $membre = null;

    #[ORM\Column]
    private ?bool $isFavorable = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $justification = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isActif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPiece(): ?PieceJointe
    {
        return $this->piece;
    }

    public function setPiece(?PieceJointe $piece): static
    {
        $this->piece = $piece;

        return $this;
    }

    public function getMembre(): ?User
    {
        return $this->membre;
    }

    public function setMembre(?User $membre): static
    {
        $this->membre = $membre;

        return $this;
    }

    public function isFavorable(): ?bool
    {
        return $this->isFavorable;
    }

    public function setFavorable(bool $isFavorable): static
    {
        $this->isFavorable = $isFavorable;

        return $this;
    }

    public function getJustification(): ?string
    {
        return $this->justification;
    }

    public function setJustification(?string $justification): static
    {
        $this->justification = $justification;

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

    public function isActif(): ?bool
    {
        return $this->isActif;
    }

    public function setActif(bool $isActif): static
    {
        $this->isActif = $isActif;

        return $this;
    }
}
