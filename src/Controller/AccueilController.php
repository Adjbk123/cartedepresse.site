<?php

namespace App\Controller;

use App\Repository\CarteRepository;
use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\OrganeRepository;
use App\Service\EmailNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AccueilController extends AbstractController
{
    private $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }
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
    public function index(DemandeRepository $demandeRepository, HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository, CarteRepository $carteRepository): Response
    {
        if ($this->getUser() and !$this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_admin');
        }

        $professionnel = $this->getUser();
        if ($this->getUser() and $this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_espace');

        }




        return $this->render('accueil/indexAccueil.html.twig', [

        ]);
    }

    #[Route('/mon-espace', name: 'app_espace')]
    public function indexEspace(
        DemandeRepository $demandeRepository,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository, CarteRepository $carteRepository
    ): Response {

        // Vérification du rôle
        if (!$this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_admin');
        }

        // Récupération de l'utilisateur actuel
        $user = $this->getUser();

        // Récupérer toutes les demandes du professionnel
        $demandes = $demandeRepository->findBy(['professionnel' => $user]);


        $demandes = $demandeRepository->findBy(['professionnel'=>$user->getId()]);

        $derniereDemande  = $demandeRepository->findOneBy(['professionnel'=>$user->getId()]);
        $derniereCarte = $carteRepository->findOneBy(['demande'=>$derniereDemande->getId()]);


        // Récupération des organes du professionnel
        $historiques = $historiqueOrganeProfessionnelRepository->findBy(['professionnel' => $user]);

        return $this->render('accueil/index.html.twig', [
            'demandes' => $demandes,
            'derniereDemande' => $derniereDemande,
            'historiques' => $historiques,
            'derniere_carte' => $derniereCarte,

        ]);
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    #[Route('/completer-plus-tard', name: 'app_espace_completer_plus_tard')]
    public function completerPlusTard(DemandeRepository $demandeRepository, HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository): Response
    {
        // Vérification du rôle de l'utilisateur connecté
        if (!$this->isGranted("ROLE_PROFESSIONNEL")) {
            return $this->redirectToRoute('app_admin');
        }

        // Récupération du professionnel (l'utilisateur connecté)
        $professionnel = $this->getUser();

        // Récupération des demandes du professionnel
        $demande = $demandeRepository->findBy(['professionnel' => $professionnel]);

        // Préparation des informations du professionnel pour l'email (nom et prénoms)
        $professionnelData = [
            'nom' => $professionnel->getNom(),
            'prenoms' => $professionnel->getPrenoms()
        ];

        // Envoi de l'email d'invitation à compléter le profil
        $this->emailNotificationService->sendMessageCompletionProfil($professionnel->getEmail(), $professionnelData);
        return $this->redirectToRoute('app_logout');


    }

}
