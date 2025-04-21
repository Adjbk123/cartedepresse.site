<?php

namespace App\Entity;

use App\Repository\ProfessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionRepository::class)]
class Profession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, HistoriqueOrganeProfessionnel>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueOrganeProfessionnel::class, mappedBy: 'profession')]
    private Collection $historiqueOrganeProfessionnels;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $code = null;

    public function __construct()
    {
        $this->historiqueOrganeProfessionnels = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

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
            $historiqueOrganeProfessionnel->setProfession($this);
        }

        return $this;
    }

    public function removeHistoriqueOrganeProfessionnel(HistoriqueOrganeProfessionnel $historiqueOrganeProfessionnel): static
    {
        if ($this->historiqueOrganeProfessionnels->removeElement($historiqueOrganeProfessionnel)) {
            // set the owning side to null (unless already changed)
            if ($historiqueOrganeProfessionnel->getProfession() === $this) {
                $historiqueOrganeProfessionnel->setProfession(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }


}
