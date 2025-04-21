<?php
namespace App\Service;

use App\Entity\HaacInfo;
use Doctrine\ORM\EntityManagerInterface;

class HaacConfigService
{
    private ?HaacInfo $config = null;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    private function loadConfig(): void
    {
        if (!$this->config) {
            // On suppose qu’il y a une seule configuration (la plus récente ou l’unique)
            $this->config = $this->entityManager->getRepository(HaacInfo::class)->findOneBy([], ['id' => 'DESC']);
        }
    }

    public function getConfig(): ?HaacInfo
    {
        $this->loadConfig();
        return $this->config;
    }

    public function getLogoPath(): ?string
    {
        return $this->getConfig()?->getLogoPath();
    }

    public function getLogoBeninPath(): ?string
    {
        return $this->getConfig()?->getLogoBeninPath();
    }

    public function getArmoiriePath(): ?string
    {
        return $this->getConfig()?->getAmoiriePath();
    }

    public function getCachetPath(): ?string
    {
        return $this->getConfig()?->getCachetPath();
    }

    public function getTextIntro(): ?string
    {
        return $this->getConfig()?->getTextIntro();
    }

    public function getMentionFinale(): ?string
    {
        return $this->getConfig()?->getMentionFinale();
    }
}
