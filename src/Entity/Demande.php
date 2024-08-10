<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateSoumission = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    private ?User $professionnel = null;

    #[ORM\Column(length: 255)]
    private ?string $numDemande = null;

    /**
     * @var Collection<int, PieceJointe>
     */
    #[ORM\OneToMany(targetEntity: PieceJointe::class, mappedBy: 'demande')]
    private Collection $pieceJointes;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    private ?Lot $lot = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeDemande = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    #[ORM\ManyToOne(inversedBy: 'demandesTraitant')]
    private ?User $agentTraitant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateTraitement = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    private ?HistoriqueOrganeProfessionnel $historiqueOrganeActuel = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isReadyForPrint = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDelivrance = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPrinted = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlFile = null;

    /**
     * @var Collection<int, DuplicataDemande>
     */
    #[ORM\OneToMany(targetEntity: DuplicataDemande::class, mappedBy: 'demande')]
    private Collection $duplicataDemandes;

    public function __construct()
    {
        $this->pieceJointes = new ArrayCollection();
        $this->duplicataDemandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProfessionnel(): ?User
    {
        return $this->professionnel;
    }

    public function setProfessionnel(?User $professionnel): static
    {
        $this->professionnel = $professionnel;

        return $this;
    }

    public function getNumDemande(): ?string
    {
        return $this->numDemande;
    }

    public function setNumDemande(string $numDemande): static
    {
        $this->numDemande = $numDemande;

        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPieceJointes(): Collection
    {
        return $this->pieceJointes;
    }

    public function addPieceJointe(PieceJointe $pieceJointe): static
    {
        if (!$this->pieceJointes->contains($pieceJointe)) {
            $this->pieceJointes->add($pieceJointe);
            $pieceJointe->setDemande($this);
        }

        return $this;
    }

    public function removePieceJointe(PieceJointe $pieceJointe): static
    {
        if ($this->pieceJointes->removeElement($pieceJointe)) {
            // set the owning side to null (unless already changed)
            if ($pieceJointe->getDemande() === $this) {
                $pieceJointe->setDemande(null);
            }
        }

        return $this;
    }

    public function getLot(): ?Lot
    {
        return $this->lot;
    }

    public function setLot(?Lot $lot): static
    {
        $this->lot = $lot;

        return $this;
    }

    public function getTypeDemande(): ?string
    {
        return $this->typeDemande;
    }

    public function setTypeDemande(?string $typeDemande): static
    {
        $this->typeDemande = $typeDemande;

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

    public function getAgentTraitant(): ?User
    {
        return $this->agentTraitant;
    }

    public function setAgentTraitant(?User $agentTraitant): static
    {
        $this->agentTraitant = $agentTraitant;

        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->dateTraitement;
    }

    public function setDateTraitement(?\DateTimeInterface $dateTraitement): static
    {
        $this->dateTraitement = $dateTraitement;

        return $this;
    }

    public function getHistoriqueOrganeActuel(): ?HistoriqueOrganeProfessionnel
    {
        return $this->historiqueOrganeActuel;
    }

    public function setHistoriqueOrganeActuel(?HistoriqueOrganeProfessionnel $historiqueOrganeActuel): static
    {
        $this->historiqueOrganeActuel = $historiqueOrganeActuel;

        return $this;
    }

    public function isReadyForPrint(): ?bool
    {
        return $this->isReadyForPrint;
    }

    public function setReadyForPrint(?bool $isReadyForPrint): static
    {
        $this->isReadyForPrint = $isReadyForPrint;

        return $this;
    }

    public function getDateDelivrance(): ?\DateTimeInterface
    {
        return $this->dateDelivrance;
    }

    public function setDateDelivrance(?\DateTimeInterface $dateDelivrance): static
    {
        $this->dateDelivrance = $dateDelivrance;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(?\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function isPrinted(): ?bool
    {
        return $this->isPrinted;
    }

    public function setPrinted(?bool $isPrinted): static
    {
        $this->isPrinted = $isPrinted;

        return $this;
    }

    public function getUrlFile(): ?string
    {
        return $this->urlFile;
    }

    public function setUrlFile(?string $urlFile): static
    {
        $this->urlFile = $urlFile;

        return $this;
    }

    /**
     * @return Collection<int, DuplicataDemande>
     */
    public function getDuplicataDemandes(): Collection
    {
        return $this->duplicataDemandes;
    }

    public function addDuplicataDemande(DuplicataDemande $duplicataDemande): static
    {
        if (!$this->duplicataDemandes->contains($duplicataDemande)) {
            $this->duplicataDemandes->add($duplicataDemande);
            $duplicataDemande->setDemande($this);
        }

        return $this;
    }

    public function removeDuplicataDemande(DuplicataDemande $duplicataDemande): static
    {
        if ($this->duplicataDemandes->removeElement($duplicataDemande)) {
            // set the owning side to null (unless already changed)
            if ($duplicataDemande->getDemande() === $this) {
                $duplicataDemande->setDemande(null);
            }
        }

        return $this;
    }
}
