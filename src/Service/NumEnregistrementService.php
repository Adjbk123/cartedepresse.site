<?php

// src/Service/NumEnregistrementService.php

namespace App\Service;

use App\Entity\Demande;
use Doctrine\ORM\EntityManagerInterface;

class NumEnregistrementService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Génère le numéro d'enregistrement pour une nouvelle demande.
     *
     * @return string
     */
    public function genererNumeroEnregistrement(): string
    {
        // Récupérer l'année en cours
        $anneeEnCours = date('Y');

        // Récupérer la dernière demande enregistrée pour l'année en cours
        $derniereDemande = $this->entityManager
            ->getRepository(Demande::class)
            ->findDerniereDemandePourAnnee($anneeEnCours);

        if (!$derniereDemande) {
            // Première demande de l'année
            $numero = sprintf('%s/HAAC/0001', $anneeEnCours);
        } else {
            // Incrémenter le numéro de la dernière demande
            $dernierNumero = $derniereDemande->getNumDemande();
            preg_match('/(\d+)$/', $dernierNumero, $matches);
            $nouveauNumero = $matches[1] + 1;
            $numero = sprintf('%s/HAAC/%04d', $anneeEnCours, $nouveauNumero);
        }

        return $numero;
    }
}
