<?php

namespace App\Entity;

use App\Repository\OrganeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrganeRepository::class)]
class Organe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contact = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $latitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\ManyToOne(inversedBy: 'organes')]
    private ?Promoteur $promoteur = null;

    #[ORM\ManyToOne(inversedBy: 'organes')]
    private ?TypeOrgane $typeOrgane = null;

    /**
     * @var Collection<int, HistoriqueOrganeProfessionnel>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueOrganeProfessionnel::class, mappedBy: 'organe')]
    private Collection $historiqueOrganeProfessionnels;

    public function __construct()
    {
        $this->historiqueOrganeProfessionnels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getPromoteur(): ?Promoteur
    {
        return $this->promoteur;
    }

    public function setPromoteur(?Promoteur $promoteur): static
    {
        $this->promoteur = $promoteur;

        return $this;
    }

    public function getTypeOrgane(): ?TypeOrgane
    {
        return $this->typeOrgane;
    }

    public function setTypeOrgane(?TypeOrgane $typeOrgane): static
    {
        $this->typeOrgane = $typeOrgane;

        return $this;
    }

    /**
     * @return Collection<int, HistoriqueOrganeProfessionnel>
     */
    public function getHistoriqueOrganeProfessionnels(): Collection
    {
        return $this->historiqueOrganeProfessionnels;
    }

    public function addHistoriqueOrganeProfessionnel(HistoriqueOrganeProfessionnel $historiqueOrganeProfessionnel): static
    {
        if (!$this->historiqueOrganeProfessionnels->contains($historiqueOrganeProfessionnel)) {
            $this->historiqueOrganeProfessionnels->add($historiqueOrganeProfessionnel);
            $historiqueOrganeProfessionnel->setOrgane($this);
        }

        return $this;
    }

    public function removeHistoriqueOrganeProfessionnel(HistoriqueOrganeProfessionnel $historiqueOrganeProfessionnel): static
    {
        if ($this->historiqueOrganeProfessionnels->removeElement($historiqueOrganeProfessionnel)) {
            // set the owning side to null (unless already changed)
            if ($historiqueOrganeProfessionnel->getOrgane() === $this) {
                $historiqueOrganeProfessionnel->setOrgane(null);
            }
        }

        return $this;
    }
}
