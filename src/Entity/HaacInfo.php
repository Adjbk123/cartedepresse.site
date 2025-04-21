<?php

namespace App\Entity;

use App\Repository\HaacInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HaacInfoRepository::class)]
class HaacInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $logoPath = null;

    #[ORM\Column(length: 255)]
    private ?string $amoiriePath = null;

    #[ORM\Column(length: 255)]
    private ?string $cachetPath = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $textIntro = null;

    #[ORM\Column(length: 255)]
    private ?string $mentionFinale = null;

    #[ORM\Column(length: 255)]
    private ?string $logoBeninPath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(string $logoPath): static
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    public function getAmoiriePath(): ?string
    {
        return $this->amoiriePath;
    }

    public function setAmoiriePath(string $amoiriePath): static
    {
        $this->amoiriePath = $amoiriePath;

        return $this;
    }

    public function getCachetPath(): ?string
    {
        return $this->cachetPath;
    }

    public function setCachetPath(string $cachetPath): static
    {
        $this->cachetPath = $cachetPath;

        return $this;
    }

    public function getTextIntro(): ?string
    {
        return $this->textIntro;
    }

    public function setTextIntro(string $textIntro): static
    {
        $this->textIntro = $textIntro;

        return $this;
    }

    public function getMentionFinale(): ?string
    {
        return $this->mentionFinale;
    }

    public function setMentionFinale(string $mentionFinale): static
    {
        $this->mentionFinale = $mentionFinale;

        return $this;
    }

    public function getLogoBeninPath(): ?string
    {
        return $this->logoBeninPath;
    }

    public function setLogoBeninPath(string $logoBeninPath): static
    {
        $this->logoBeninPath = $logoBeninPath;

        return $this;
    }
}
