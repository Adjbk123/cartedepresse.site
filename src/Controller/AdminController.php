<?php

namespace App\Controller;

use App\Entity\Lot;
use App\Repository\DemandeRepository;
use App\Repository\LotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(DemandeRepository $demandeRepository, LotRepository $lotRepository): Response
    {


        if ($this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_accueil');
        }

        $totalDemandes = $demandeRepository->count([]);
        $demandesEnCours = $demandeRepository->count(['statut' => 'En attente']);
        $demandesRejetees = $demandeRepository->count(['statut' => 'Rejetée']);
        $demandesValidees = $demandeRepository->count(['statut' => 'Validée']);
        $cartesImprimees = $demandeRepository->count(['isPrinted' => true]);
        $cartesExpirees = $demandeRepository->countExpiredCards();
        $lotDemande = $lotRepository->count([]);

        $years = $demandeRepository->getAvailableYears();

        // Récupérer toutes les dates de début des suivis
        $datesSoumission = $demandeRepository->getAvailableYears();

        // Filtrer les valeurs null
        $datesDebut = array_filter($datesSoumission, function($date) {
            return $date !== null;
        });

        // Extraire les années des dates de début
        $annees = array_unique(array_map(function($date) {
            return $date->format('Y');
        }, $datesDebut));
        // Trier les années
        sort($annees);


        //statistique des impressions de l'année
        $totalImpressionsCurrentYear = $demandeRepository->getTotalImpressionsForCurrentYear();
        $totalImpressions = $demandeRepository->getTotalImpressions();



        return $this->render('admin/index.html.twig', [
            'totalDemandes' => $totalDemandes,
            'demandesEnCours' => $demandesEnCours,
            'demandesRejetees' => $demandesRejetees,
            'demandesValidees' => $demandesValidees,
            'cartesImprimees' => $cartesImprimees,
            'cartesExpirees' => $cartesExpirees,
            'years' => $annees,
            'totalImpressionsCurrentYear' => $totalImpressionsCurrentYear,
            'totalImpressions' => $totalImpressions,
            'lotDemande' => $lotDemande,
        ]);
    }



    #[Route('/statistics/data/{year}', name: 'app_statistics_data')]
    public function statisticsData(int $year, DemandeRepository $demandeRepository): JsonResponse
    {
        $data = $demandeRepository->getStatisticsByYear($year);

        return new JsonResponse($data);
    }

    #[Route('/admin/statistics/impressions/{year}', name: 'admin_statistics_impressions')]
    public function impressionsStatistics(int $year, DemandeRepository $demandeRepository): JsonResponse
    {
        $data = $demandeRepository->getImpressionsByMonth($year);
        return new JsonResponse($data);
    }

}
