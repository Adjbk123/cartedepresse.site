<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointes')]
    private ?Demande $demande = null;


    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSoumission = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointes')]
    private ?TypePiece $typePiece = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $observation = null;

    /**
     * @var Collection<int, PieceJointeAvisMembre>
     */
    #[ORM\OneToMany(targetEntity: PieceJointeAvisMembre::class, mappedBy: 'piece')]
    private Collection $pieceJointeAvisMembres;

    public function __construct()
    {
        $this->pieceJointeAvisMembres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): static
    {
        $this->demande = $demande;

        return $this;
    }




    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDateSoumission(): ?\DateTimeInterface
    {
        return $this->dateSoumission;
    }

    public function setDateSoumission(\DateTimeInterface $dateSoumission): static
    {
        $this->dateSoumission = $dateSoumission;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTypePiece(): ?TypePiece
    {
        return $this->typePiece;
    }

    public function setTypePiece(?TypePiece $typePiece): static
    {
        $this->typePiece = $typePiece;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): static
    {
        $this->observation = $observation;

        return $this;
    }

    /**
     * @return Collection<int, PieceJointeAvisMembre>
     */
    public function getPieceJointeAvisMembres(): Collection
    {
        return $this->pieceJointeAvisMembres;
    }

    public function addPieceJointeAvisMembre(PieceJointeAvisMembre $pieceJointeAvisMembre): static
    {
        if (!$this->pieceJointeAvisMembres->contains($pieceJointeAvisMembre)) {
            $this->pieceJointeAvisMembres->add($pieceJointeAvisMembre);
            $pieceJointeAvisMembre->setPiece($this);
        }

        return $this;
    }

    public function removePieceJointeAvisMembre(PieceJointeAvisMembre $pieceJointeAvisMembre): static
    {
        if ($this->pieceJointeAvisMembres->removeElement($pieceJointeAvisMembre)) {
            // set the owning side to null (unless already changed)
            if ($pieceJointeAvisMembre->getPiece() === $this) {
                $pieceJointeAvisMembre->setPiece(null);
            }
        }

        return $this;
    }
}
