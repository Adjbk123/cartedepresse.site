<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Un compte existe déjà avec ce mail')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenoms = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $statut = null;


    /**
     * @var Collection<int, Demande>
     */
    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'professionnel')]
    private Collection $demandes;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lieuNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $npi = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sexe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationalite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    /**
     * @var Collection<int, HistoriqueOrganeProfessionnel>
     */
    #[ORM\OneToMany(targetEntity: HistoriqueOrganeProfessionnel::class, mappedBy: 'professionnel')]
    private Collection $historiqueOrganeProfessionnels;

    /**
     * @var Collection<int, Demande>
     */
    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'agentTraitant')]
    private Collection $demandesTraitant;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    /**
     * @var Collection<int, Carte>
     */
    #[ORM\OneToMany(targetEntity: Carte::class, mappedBy: 'imprimerPar')]
    private Collection $cartes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
        $this->historiqueOrganeProfessionnels = new ArrayCollection();
        $this->demandesTraitant = new ArrayCollection();
        $this->cartes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }



    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenoms(): ?string
    {
        return $this->prenoms;
    }

    public function setPrenoms(string $prenoms): static
    {
        $this->prenoms = $prenoms;

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

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }



    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setProfessionnel($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getProfessionnel() === $this) {
                $demande->setProfessionnel(null);
            }
        }

        return $this;
    }

    public function getDateNaissance(): ?\DateTimeInterface
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTimeInterface $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getLieuNaissance(): ?string
    {
        return $this->lieuNaissance;
    }

    public function setLieuNaissance(?string $lieuNaissance): static
    {
        $this->lieuNaissance = $lieuNaissance;

        return $this;
    }

    public function getNpi(): ?string
    {
        return $this->npi;
    }

    public function setNpi(?string $npi): static
    {
        $this->npi = $npi;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getNationalite(): ?string
    {
        return $this->nationalite;
    }

    public function setNationalite(?string $nationalite): static
    {
        $this->nationalite = $nationalite;

        return $this;
    }




    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

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
            $historiqueOrganeProfessionnel->setProfessionnel($this);
        }

        return $this;
    }

    public function removeHistoriqueOrganeProfessionnel(HistoriqueOrganeProfessionnel $historiqueOrganeProfessionnel): static
    {
        if ($this->historiqueOrganeProfessionnels->removeElement($historiqueOrganeProfessionnel)) {
            // set the owning side to null (unless already changed)
            if ($historiqueOrganeProfessionnel->getProfessionnel() === $this) {
                $historiqueOrganeProfessionnel->setProfessionnel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandesTraitant(): Collection
    {
        return $this->demandesTraitant;
    }

    public function addDemandesTraitant(Demande $demandesTraitant): static
    {
        if (!$this->demandesTraitant->contains($demandesTraitant)) {
            $this->demandesTraitant->add($demandesTraitant);
            $demandesTraitant->setAgentTraitant($this);
        }

        return $this;
    }

    public function removeDemandesTraitant(Demande $demandesTraitant): static
    {
        if ($this->demandesTraitant->removeElement($demandesTraitant)) {
            // set the owning side to null (unless already changed)
            if ($demandesTraitant->getAgentTraitant() === $this) {
                $demandesTraitant->setAgentTraitant(null);
            }
        }

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

    /**
     * @return Collection<int, Carte>
     */
    public function getCartes(): Collection
    {
        return $this->cartes;
    }

    public function addCarte(Carte $carte): static
    {
        if (!$this->cartes->contains($carte)) {
            $this->cartes->add($carte);
            $carte->setImprimerPar($this);
        }

        return $this;
    }

    public function removeCarte(Carte $carte): static
    {
        if ($this->cartes->removeElement($carte)) {
            // set the owning side to null (unless already changed)
            if ($carte->getImprimerPar() === $this) {
                $carte->setImprimerPar(null);
            }
        }

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }


}
