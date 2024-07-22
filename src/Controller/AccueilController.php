<?php

namespace App\Controller;

use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\OrganeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(DemandeRepository $demandeRepository, HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository): Response
    {

        if (!$this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_admin');
        }

        $user = $this->getUser();
        $demande = $demandeRepository->findBy(['professionnel' => $user]);

        //recuperation des organes de la professionnel
        $historiques = $historiqueOrganeProfessionnelRepository->findBy(['professionnel' => $user]);



        return $this->render('accueil/index.html.twig', [
            'demandes' => $demande,
            'historiques' => $historiques,

        ]);
    }
}
