<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\HistoriqueOrganeProfessionnel;
use App\Entity\PieceJointe;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\PieceJointeRepository;
use App\Repository\TypePieceRepository;
use App\Repository\UserRepository;
use App\Service\EmailNotificationService;
use App\Service\NumEnregistrementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demande')]
class DemandeController extends AbstractController
{
    private $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }

    #[Route('/', name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }
    #[Route('/traitee', name: 'app_demande_traitee', methods: ['GET'])]
    public function indexDemandeTraitee(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexDemandeValidee.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"Validée"]),
        ]);
    }
    #[Route('/rejetee', name: 'app_demande_rejetee', methods: ['GET'])]
    public function indexDemandeRejetee(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexDemandeRejetee.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"Rejetée"]),
        ]);
    }

    #[Route('/{id}/piece-reupload', name: 'app_piece_reupload', methods: ['POST'])]
    public function reuploadPieceJointe(PieceJointe $pieceJointe, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Gestion de l'upload du nouveau fichier
        $fichier = $request->files->get('fichier');

        $user = $this->getUser();
        if ($fichier) {
            $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $url = uniqid() . '_' . $user->getNom() . '_' . $user->getPrenoms() . '_' . $nomFichier;

            $directory = 'uploads/' . date('Y') . '/' . date('m') . '/' . date('d');
            $fichier->move($directory, $url);

            // Mettre à jour les informations de la pièce jointe
            $pieceJointe->setUrl($url);
            $pieceJointe->setDateSoumission(new \DateTime());
            $pieceJointe->setStatut('En attente');
            $entityManager->flush();
        }

        $this->addFlash('success', 'Votre pièce jointe a été rechargée avec succès.');
        return $this->redirectToRoute('app_accueil');
    }

 #[Route('/attente', name: 'app_demande_en_attente', methods: ['GET'])]
    public function indexAttente(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexAttente.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"En attente"]),
        ]);
    }

    #[Route('/nouvelle-demande', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        UserRepository $userRepository,
                        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
                    TypePieceRepository $typePieceRepository,
                        NumEnregistrementService $numEnregistrementService
    ): Response
    {

        //recuperation du professionnel connecté
        $user = $this->getUser();
        $professionnel = $userRepository->findOneBy(['id' => $user]);


        // type de piece pour une nouvelle demande
        $typePiece = $typePieceRepository->findBy(['typeDemande' => "Nouveau"]);

        $demande = new Demande();

        if ($request->isMethod('POST')) {
            $data = $request->files->get('typePieces');
            // Générer le numéro d'enregistrement
            $numeroEnregistrement = $numEnregistrementService->genererNumeroEnregistrement();
            $demande->setNumDemande($numeroEnregistrement);
            $demande->setStatut('En attente');
            $demande->setProfessionnel($professionnel);
            $demande->setDateSoumission(new \DateTime());
            $demande->setTypeDemande('Etablissement');

            $historiqueOrganeActuel = $historiqueOrganeProfessionnelRepository->findBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);
            $demande->setHistoriqueOrganeActuel($historiqueOrganeActuel[0]);


            // Enregistrer les pièces jointes
            foreach ($data as $key => $pieceJointe) {
                $nomFichier = $pieceJointe->getClientOriginalName();
                $typePieceId = $key; // Supposant que $key est l'ID du type de pièce
                $typePiece = $typePieceRepository->find($typePieceId); // Récupérer l'entité TypePiece correspondante
                $dateSoumission = new \DateTime();
                $statut = 'En attente';
                $url = uniqid() . '_' . $user->getNom() . '_' . $user->getPrenoms() . '_' . $nomFichier;

                $directory = 'uploads/' . date('Y') . '/' . date('m') . '/' . date('d');
                $pieceJointe->move($directory, $url);

                $piece = new PieceJointe();
                $piece->setDemande($demande);
                $piece->setUrl($url);
                $piece->setTypePiece($typePiece);
                $piece->setDateSoumission($dateSoumission);
                $piece->setStatut($statut);

                $entityManager->persist($piece);
            }



            $entityManager->persist($demande);
            $entityManager->flush();


            // Envoyer l'email de notification
            $demandeData = [
                'nom' => $demande->getProfessionnel()->getNom(). " " . $demande->getProfessionnel()->getPrenoms(),
                'type' => "Demande d'établissement de carte",
                'dateSoumission' => $demande->getDateSoumission(),
                'statut' => $demande->getStatut(),
            ];
            $this->emailNotificationService->sendDemandSubmissionEmail($professionnel->getEmail(), $demandeData);

            $this->addFlash('success', 'Votre demande a été soumise avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        //recuperation des historiques d'organe professionnel
        $historiqueOrganeProfessionnel = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel]);
        return $this->render('demande/new.html.twig', [
            'demande' => $demande,
            'professionnel' => $professionnel,
            'historiqueOrganeProfessionnel' => $historiqueOrganeProfessionnel,
            "typePieces" => $typePiece,

        ]);
    }
    #[Route('/{id}/traiter', name: 'app_demande_traiter', methods: ['GET'])]
    public function traiter(Demande $demande, PieceJointeRepository $pieceJointeRepository): Response
    {

        $fichiers = $pieceJointeRepository->findBy(['demande' => $demande]);


        return $this->render('demande/traiter.html.twig', [
            'demande' => $demande,
            'fichiers' => $fichiers,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/fichier-valider', name: 'app_fichier_valider', methods: ['POST'])]
    public function validerFichier(PieceJointe $pieceJointe, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        $observation = $request->request->get('observation');
        $pieceJointe->setStatut('Validé');
        $pieceJointe->setObservation($observation);
        $entityManager->flush();

        // Vérifier si tous les fichiers de la demande sont validés
        $demande = $pieceJointe->getDemande();
        $tousFichiersValides = true;

        foreach ($demande->getPieceJointes() as $pj) {
            if ($pj->getStatut() !== 'Validé') {
                $tousFichiersValides = false;
                break;
            }
        }

        if ($tousFichiersValides) {
            // agent traitant
            $user = $this->getUser();
            $demande->setAgentTraitant($user);

            // date de validation
            $demande->setDateTraitement(new \DateTime());

            $demande->setStatut('Validée');
            $entityManager->flush();
            $this->addFlash('success', 'Tous les fichiers de la demande ont été validés avec succès.');
            return $this->redirectToRoute('app_demande_index');
        }

        $this->addFlash('success', 'Le fichier a été validé avec succès.');
        return $this->redirectToRoute('app_demande_traiter', ['id' => $demande->getId()]);
    }

    #[Route('/{id}/fichier-rejeter', name: 'app_fichier_rejeter', methods: ['POST'])]
    public function rejeterFichier(PieceJointe $pieceJointe, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        $observation = $request->request->get('observation');
        $pieceJointe->setStatut('Rejetée');
        $pieceJointe->setObservation($observation);
        $entityManager->flush();

        // Vérifier si tous les fichiers de la demande sont rejetés
        $demande = $pieceJointe->getDemande();
        $tousFichiersRejetes = true;

        foreach ($demande->getPieceJointes() as $pj) {
            if ($pj->getStatut() !== 'Rejetée') {
                $tousFichiersRejetes = false;
                break;
            }
        }

        if ($tousFichiersRejetes) {
            // agent traitant
            $user = $this->getUser();
            $demande->setAgentTraitant($user);
            // date de validation
            $demande->setDateTraitement(new \DateTime());
            $demande->setStatut('Rejetée');
            $this->addFlash('success', 'Tous les fichiers de la demande ont été rejetés.');
            return $this->redirectToRoute('app_demande_index');
        }
        $emailProfessionnel = $demande->getProfessionnel()->getEmail();
        $professionnel= $demande->getProfessionnel()->getNom()." ".$demande->getProfessionnel()->getPrenoms();
        $observation =  $pieceJointe->getObservation();
        $piece = $pieceJointe->getTypePiece()->getLibelle();
        // Envoyer l'email de notification de rejet
        $this->emailNotificationService->sendRejectionPieceNotification($emailProfessionnel,  $observation,$professionnel, $piece);

        $this->addFlash('success', 'Le fichier a été rejeté avec succès.');
        return $this->redirectToRoute('app_demande_traiter', ['id' => $demande->getId()]);
    }

    #[Route('/{id}/demande-valider', name: 'app_demande_valider', methods: ['POST'])]
    public function validerDemande(Request $request,Demande $demande, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        foreach ($demande->getPieceJointes() as $pieceJointe) {
            $pieceJointe->setStatut('Validée');
        }
        $demande->setStatut('Validée');
        $observation = $request->request->get('observation');
        // agent traitant
        $user = $this->getUser();
        $demande->setAgentTraitant($user);

        // date de validation
        $demande->setDateTraitement(new \DateTime());
        $demande->setObservation($observation);
        $entityManager->flush();

        $this->addFlash('success', 'La demande a été validée avec succès.');
        return $this->redirectToRoute('app_demande_en_attente');
    }

    #[Route('/{id}/demande-rejeter', name: 'app_demande_rejeter', methods: ['POST'])]
    public function rejeterDemande(Request $request, Demande $demande, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        foreach ($demande->getPieceJointes() as $pieceJointe) {
            $pieceJointe->setStatut('Rejetée');
        }
        $demande->setStatut('Rejetée');
        $observation = $request->request->get('observation');

        // agent traitant
        $user = $this->getUser();
        $demande->setAgentTraitant($user);
        $demande->setObservation($observation);
        $entityManager->flush();


        $emailProfessionnel = $demande->getProfessionnel()->getEmail();
        $professionnel= $demande->getProfessionnel()->getNom()." ".$demande->getProfessionnel()->getPrenoms();
        $observation = $demande->getObservation();
        // Envoyer l'email de notification de rejet
        $this->emailNotificationService->sendRejectionDemandeNotification($emailProfessionnel, $observation,$professionnel);

        $this->addFlash('success', 'La demande a été rejetée avec succès.');
        return $this->redirectToRoute('app_demande_index');
    }

    #[Route('/{id}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
    }

}
