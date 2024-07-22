<?php

namespace App\Entity;

use App\Repository\TypeOrganeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeOrganeRepository::class)]
class TypeOrgane
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Organe>
     */
    #[ORM\OneToMany(targetEntity: Organe::class, mappedBy: 'typeOrgane')]
    private Collection $organes;

    public function __construct()
    {
        $this->organes = new ArrayCollection();
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
     * @return Collection<int, Organe>
     */
    public function getOrganes(): Collection
    {
        return $this->organes;
    }

    public function addOrgane(Organe $organe): static
    {
        if (!$this->organes->contains($organe)) {
            $this->organes->add($organe);
            $organe->setTypeOrgane($this);
        }

        return $this;
    }

    public function removeOrgane(Organe $organe): static
    {
        if ($this->organes->removeElement($organe)) {
            // set the owning side to null (unless already changed)
            if ($organe->getTypeOrgane() === $this) {
                $organe->setTypeOrgane(null);
            }
        }

        return $this;
    }
}
