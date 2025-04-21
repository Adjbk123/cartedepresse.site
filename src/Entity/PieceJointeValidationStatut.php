<?php

namespace App\Entity;

use App\Repository\PieceJointeValidationStatutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeValidationStatutRepository::class)]
class PieceJointeValidationStatut
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PieceJointe $piece = null;

    #[ORM\Column]
    private ?int $nbAvisTotal = null;

    #[ORM\Column]
    private ?int $nbFavorable = null;

    #[ORM\Column(length: 255)]
    private ?string $statutValidation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbDefavorable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPiece(): ?PieceJointe
    {
        return $this->piece;
    }

    public function setPiece(PieceJointe $piece): static
    {
        $this->piece = $piece;

        return $this;
    }

    public function getNbAvisTotal(): ?int
    {
        return $this->nbAvisTotal;
    }

    public function setNbAvisTotal(int $nbAvisTotal): static
    {
        $this->nbAvisTotal = $nbAvisTotal;

        return $this;
    }

    public function getNbFavorable(): ?int
    {
        return $this->nbFavorable;
    }

    public function setNbFavorable(int $nbFavorable): static
    {
        $this->nbFavorable = $nbFavorable;

        return $this;
    }

    public function getStatutValidation(): ?string
    {
        return $this->statutValidation;
    }

    public function setStatutValidation(string $statutValidation): static
    {
        $this->statutValidation = $statutValidation;

        return $this;
    }

    public function getNbDefavorable(): ?int
    {
        return $this->nbDefavorable;
    }

    public function setNbDefavorable(?int $nbDefavorable): static
    {
        $this->nbDefavorable = $nbDefavorable;

        return $this;
    }
}
