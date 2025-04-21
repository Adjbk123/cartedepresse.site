<?php

namespace App\Entity;

use App\Repository\CarteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteRepository::class)]
class Carte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'carte', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $demande = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDelivrance = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateExpiration = null;

    #[ORM\ManyToOne(inversedBy: 'cartes')]
    private ?User $imprimerPar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlFile = null;

    #[ORM\Column(length: 255)]
    private ?string $numCarte = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPrinted = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(Demande $demande): static
    {
        $this->demande = $demande;

        return $this;
    }

    public function getDateDelivrance(): ?\DateTimeInterface
    {
        return $this->dateDelivrance;
    }

    public function setDateDelivrance(\DateTimeInterface $dateDelivrance): static
    {
        $this->dateDelivrance = $dateDelivrance;

        return $this;
    }

    public function getDateExpiration(): ?\DateTimeInterface
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTimeInterface $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    public function getImprimerPar(): ?User
    {
        return $this->imprimerPar;
    }

    public function setImprimerPar(?User $imprimerPar): static
    {
        $this->imprimerPar = $imprimerPar;

        return $this;
    }

    public function getUrlFile(): ?string
    {
        return $this->urlFile;
    }

    public function setUrlFile(string $urlFile): static
    {
        $this->urlFile = $urlFile;

        return $this;
    }

    public function getNumCarte(): ?string
    {
        return $this->numCarte;
    }

    public function setNumCarte(string $numCarte): static
    {
        $this->numCarte = $numCarte;

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
}
