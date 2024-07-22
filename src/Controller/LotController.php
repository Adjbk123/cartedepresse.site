<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\Lot;
use App\Form\LotType;
use App\Repository\DemandeRepository;
use App\Repository\LotRepository;
use App\Service\EmailNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lot')]
class LotController extends AbstractController
{
    #[Route('/', name: 'app_lot_index', methods: ['GET'])]
    public function index(LotRepository $lotRepository): Response
    {
        return $this->render('lot/index.html.twig', [
            'lots' => $lotRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_lot_new', methods: ['GET', 'POST'])]
    public function new(EmailNotificationService $emailNotificationService, Request $request, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository, Pdf $pdf): Response
    {
        $demandesValidees = $demandeRepository->findBy(['statut' => ['Validée', 'Rejétée'], 'lot' => null]);

        if ($request->isMethod('POST')) {
            $lot = new Lot();
            $data = $request->request->all();
            $demandesIds = $data['demandes'];

            // Récupérer les entités de demandes
            $demandes = $demandeRepository->findBy(['id' => $demandesIds]);

            // Associer les demandes sélectionnées au lot
            foreach ($demandes as $demande) {
                $demande->setLot($lot);
            }

            $lot->setDateCreation(new \DateTime());
            $lot->setStatut("En attente");


            // Génération du PDF en mode paysage
            $html = $this->renderView('demande/rapport_lot.html.twig', [
                'lot' => $lot,
                'demandes' => $demandes,
            ]);

            // Options pour KNPSnappy (PDF en mode paysage)
            $options = [
                'orientation' => 'landscape',
            ];

            // Générer le contenu PDF avec KNPSnappy en mode paysage
            $pdfContent = $pdf->getOutputFromHtml($html, $options);

            // Chemin où enregistrer le fichier PDF
            $pdfPath = 'uploads/rapports/';

            // Nom de fichier basé sur la date et l'heure actuelle
            $filename = 'rapport_lot_' . date('Y-m-d_H-i-s') . '.pdf';

            // Enregistrer le fichier PDF dans le répertoire uploads/rapports
            file_put_contents($pdfPath . $filename, $pdfContent);


          $emailNotificationService->sendLotCreatedEmail($lot);
              $lot->setRapport($filename);
            $entityManager->persist($lot);

            $entityManager->flush();

            // Rediriger vers la liste des lots après génération du PDF
            $this->addFlash('success', 'Le lot a été créé avec succès.');
            return $this->redirectToRoute('app_lot_index');
        }

        return $this->render('lot/new.html.twig', [
            'demandes' => $demandesValidees,
        ]);
    }




    #[Route('/{id}', name: 'app_lot_show', methods: ['GET'])]
    public function show(Lot $lot): Response
    {
        return $this->render('lot/show.html.twig', [
            'lot' => $lot,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lot $lot, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository): Response
    {


        if ($request->isMethod('POST')) {
            // Gestion de l'upload du nouveau fichier
            $fichier = $request->files->get('decision');

            if ($fichier) {
                $filename = 'decision_lot_'. $lot->getId() . date('Y-m-d_H-i-s') . '.pdf';
                $directory = 'uploads' .  '/' . 'decisions' ;
                $fichier->move($directory, $filename);

                $lot->setStatut('Validé');
                $lot->setDecision($filename);

            }

            // Récupérer les entités de demandes
            $demandes = $demandeRepository->findBy(['lot' => $lot]);

            // Associer les demandes sélectionnées au lot
            foreach ($demandes as $demande) {
                $demande->setReadyForPrint(true);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Le lot a été validé avec succès.');

            return $this->redirectToRoute('app_lot_index', [], Response::HTTP_SEE_OTHER);
        }


        return $this->render('lot/edit.html.twig', [
            'lot' => $lot,
        ]);
    }

    #[Route('/{id}', name: 'app_lot_delete', methods: ['POST'])]
    public function delete(Request $request, Lot $lot, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lot->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lot);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lot_index', [], Response::HTTP_SEE_OTHER);
    }
}
