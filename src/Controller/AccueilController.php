<?php

namespace App\Controller;

use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\OrganeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/erreur', name: 'error')]
    public function show(\Throwable $exception): Response
    {
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            'status_code' => $statusCode,
            'message' => $exception->getMessage(),
        ]);
    }
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
