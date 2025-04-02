<?php

namespace App\Controller;

use App\Entity\Carte;
use App\Entity\Demande;
use App\Repository\CarteRepository;
use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\PresidentRepository;
use App\Service\PdfService;
use App\Service\QrCodeService;
use Doctrine\ORM\EntityManagerInterface;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImpressionController extends AbstractController
{
    #[Route('/impression', name: 'app_impression')]
    public function index(CarteRepository $carteRepository): Response
    {

        $cartePretes = $carteRepository->findBy(['imprimerPar'=>null]);

        return $this->render('impression/index.html.twig', [
            'cartePretes' => $cartePretes,
        ]);
    }

/*

    #[Route('/print/card/save/{id}', name: 'app_print_card')]
    public function saveCard(
        Demande $demande,
        PresidentRepository $presidentRepository,
        Pdf $pdf,
        EntityManagerInterface $entityManager,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        UrlGeneratorInterface $router
    ): Response
    {
        // Récupère le dossier d'upload
        $uploadDir = $this->getParameter('uploadDir');

        if (!$demande) {
            throw $this->createNotFoundException('La demande demandée n\'existe pas.');
        }

        // Création d'une nouvelle carte
        $carte = new Carte();
        $carte->setDateDelivrance(new \DateTime());
        $carte->setDemande($demande);
        $dateDelivrance = $carte->getDateDelivrance();
        $dateExpiration = (clone $dateDelivrance)->modify('+3 years');
        $carte->setDateExpiration($dateExpiration);
        $carte->setImprimerPar($this->getUser());

        // Enregistrement de la carte dans la base de données
        $entityManager->persist($carte);
        $entityManager->flush();

        // Récupère le président actuel
        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        // Format du numéro de la demande
        $numDemande = str_replace('/', '-', $demande->getNumDemande());

        // Génération du lien pour le QR code
        $qrCodeUrl = $router->generate('app_card_detail', ['numDemande' => $numDemande], UrlGeneratorInterface::ABSOLUTE_URL);
        $qrCode = new QrCode($qrCodeUrl);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);
        $qrCodeDataUri = 'data:image/png;base64,' . base64_encode($qrCodeResult->getString());

        // Récupère la profession du professionnel
        $professionnel = $demande->getProfessionnel();
        $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);

        // Rendu du contenu HTML pour le PDF
        $html = $this->renderView('impression/card.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president'=> $presidentActuel,
        ]);

        // Nom du fichier PDF
        $filename = sprintf('carte-%s.pdf', $numDemande);

        // Mise à jour de la demande pour marquer comme imprimée
        $demande->setPrinted(1);
        $carte->setUrlFile($filename);
        $entityManager->persist($carte); // Persist de la carte avec le chemin du fichier
        $entityManager->flush(); // Enregistrement dans la base de données

        // Options pour la génération du PDF
        $options = [
            'page-width' => '85.60mm',
            'page-height' => '53.98mm',
            'margin-top' => 0,
            'margin-right' => 0,
            'margin-bottom' => 0,
            'margin-left' => 0,
            'disable-local-file-access' => true,
        ];

        try {
            // Génération du contenu PDF
            $pdfContent = $pdf->getOutputFromHtml($html, $options);
        } catch (\Exception $e) {
            return new Response(
                'Error generating PDF: ' . $e->getMessage(),
                500
            );
        }

        // Sauvegarde du fichier PDF dans le dossier spécifié
        $filePath = $uploadDir . '/' . $filename;
        file_put_contents($filePath, $pdfContent);

        // Redirige vers l'aperçu de l'impression
        return $this->redirectToRoute('app_impression_apercu', ['id' => $carte->getId()]);
    }
*/

    #[Route('/print/card/test/{id}', name: 'app_print_card_test')]
    public function testCardGeneration(
        Demande $demande,
        PresidentRepository $presidentRepository,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        QrCodeService $qrCodeService,
        PdfService $pdfService
    ): Response
    {
        if (!$demande) {
            throw $this->createNotFoundException('La demande demandée n\'existe pas.');
        }

        // Récupérer la carte existante associée à la demande
        $carte = $demande->getCarte();
        if (!$carte) {
            throw $this->createNotFoundException('La carte associée à cette demande n\'existe pas.');
        }

        // Générer le PDF sans enregistrer les modifications
        $pdfContent = $this->generatePdfContent($carte, $pdfService, $qrCodeService, $historiqueOrganeProfessionnelRepository, $presidentRepository);

        // Retourner le PDF généré en tant que réponse HTTP
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="carte_test.pdf"',
            ]
        );
    }
    private function generatePdfContent(
        Carte $carte,
        PdfService $pdfService,
        QrCodeService $qrCodeService,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        PresidentRepository $presidentRepository
    ): string
    {
        $demande = $carte->getDemande();
        $numDemande = str_replace('/', '-', $demande->getNumDemande());
        $qrCodeDataUri = $qrCodeService->generateQrCodeUrl($numDemande);

        $professionnel = $demande->getProfessionnel();
        $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);
        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        $html = $this->renderView('impression/cardD.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ]);

        return $pdfService->generatePdf('impression/cardD.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ], [0, 0, 242.65, 153.98]);
    }
    #[Route('/print/card/save/{id}', name: 'app_print_card_second')]
    public function saveCardSecond(
        Carte $carte,
        PresidentRepository $presidentRepository,
        EntityManagerInterface $entityManager,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        QrCodeService $qrCodeService,
        PdfService $pdfService
    ): Response
    {

        // Mettre à jour la carte si nécessaire
        $carte->setImprimerPar($this->getUser());
        $entityManager->persist($carte);
        $entityManager->flush();

        // Générer et sauvegarder le PDF si ce n'est pas déjà fait
        if (!$carte->getUrlFile()) {
            $this->generateAndSavePdf($carte, $pdfService, $qrCodeService, $historiqueOrganeProfessionnelRepository, $presidentRepository, $entityManager);
        }

        // Rediriger vers l'aperçu de l'impression
        return $this->redirectToRoute('app_impression_apercu', ['id' => $carte->getId()]);
    }

    private function generateAndSavePdf(
        Carte $carte,
        PdfService $pdfService,
        QrCodeService $qrCodeService,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        PresidentRepository $presidentRepository,
        EntityManagerInterface $entityManager
    ): void
    {
        $demande = $carte->getDemande();
        $numDemande = str_replace('/', '-', $demande->getNumDemande());
        $qrCodeDataUri = $qrCodeService->generateQrCodeUrl($numDemande);

        $professionnel = $demande->getProfessionnel();
        $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);
        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        $html = $this->renderView('impression/cardD.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ]);

        $pdfContent = $pdfService->generatePdf('impression/cardD.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ], [0, 0, 242.65, 153.98]);

        $uploadDir = $this->getParameter('uploadDir');
        $filename = sprintf('carte-%s.pdf', $numDemande);
        $filePath = $uploadDir . '/' . $filename;
        file_put_contents($filePath, $pdfContent);

        $carte->setUrlFile($filename);
        $demande->setPrinted(1);

        $entityManager->persist($carte);
        $entityManager->flush();
    }

    /*
    #[Route('/print/carte/save/{id}', name: 'app_print_card_second')]
    public function saveCardSecond(
        Demande $carte,
        PresidentRepository $presidentRepository,
        EntityManagerInterface $entityManager,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        UrlGeneratorInterface $router
    ): Response {
        $uploadDir = $this->getParameter('uploadDir');

        if (!$carte) {
            throw $this->createNotFoundException('La carte demandée n\'existe pas.');
        }

        // Mise à jour des dates
        $carte->setDateDelivrance(new \DateTime());
        $dateDelivrance = $carte->getDateDelivrance();
        $dateExpiration = (clone $dateDelivrance)->modify('+3 years');
        $carte->setDateExpiration($dateExpiration);
        $entityManager->flush();

        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        // Génération du QR Code
        $numDemande = str_replace('/', '-', $carte->getNumDemande());
        $qrCodeUrl = $router->generate('app_card_detail', ['numDemande' => $numDemande], UrlGeneratorInterface::ABSOLUTE_URL);

        $qrCode = new QrCode($qrCodeUrl);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);
        $qrCodeDataUri = 'data:image/png;base64,' . base64_encode($qrCodeResult->getString());

        // Récupération des données nécessaires pour la vue
        $professionnel = $carte->getProfessionnel();
        $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);

        $html = $this->renderView('impression/cardD.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ]);

        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Configuration des dimensions pour une carte de visite
        $customPaper = [0, 0, 242.65, 153.98]; // 85.60 mm x 53.98 mm en points
        $dompdf->setPaper($customPaper);

        // Générer le PDF
        $dompdf->render();

        // Sauvegarde du fichier PDF
        $filename = sprintf('carte-%s.pdf', $numDemande);
        $filePath = $uploadDir . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());

        // Mise à jour des informations de la carte
        $carte->setPrinted(1);
        $carte->setUrlFile($filename);
        $entityManager->flush();

        return $this->redirectToRoute('app_impression_apercu', ['id' => $carte->getId()]);
    }

*/
    #[Route('/print/card/preview/{id}', name: 'app_print_card_preview')]
    public function previewCard(
        Carte $carte,
        PresidentRepository $presidentRepository,
        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
        UrlGeneratorInterface $router
    ): Response {
        if (!$carte) {
            throw $this->createNotFoundException('La carte demandée n\'existe pas.');
        }

        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        $numDemande = str_replace('/', '-', $carte->getDemande()->getNumDemande());

        // Génération du QR Code
        $qrCodeUrl = $router->generate('app_card_detail', ['numDemande' => $numDemande], UrlGeneratorInterface::ABSOLUTE_URL);

        $qrCode = new QrCode($qrCodeUrl);
        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);
        $qrCodeDataUri = 'data:image/png;base64,' . base64_encode($qrCodeResult->getString());

        // Récupération des données pour la vue
        $professionnel = $carte->getDemande()->getProfessionnel();
        $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);

        $html = $this->renderView('impression/card.html.twig', [
            'carte' => $carte,
            'profession' => $profession,
            'qrCode' => $qrCodeDataUri,
            'president' => $presidentActuel,
        ]);

        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Configuration des dimensions pour une carte de visite
        $customPaper = [0, 0, 242.65, 153.98]; // 85.60 mm x 53.98 mm en points
        $dompdf->setPaper($customPaper);

        // Générer le PDF
        $dompdf->render();

        // Retourner le PDF pour prévisualisation directe
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview-carte.pdf"',
        ]);
    }



    /*
        #[Route('/print/card/preview/{id}', name: 'app_print_card_preview')]
        public function previewCard(Demande $carte,
                                    PresidentRepository $presidentRepository,
                                    Pdf $pdf,
                                    HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
                                    UrlGeneratorInterface $router): Response
        {
            if (!$carte) {
                throw $this->createNotFoundException('La carte demandée n\'existe pas.');
            }

            $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

            $numDemande = str_replace('/', '-', $carte->getNumDemande());

            $qrCodeUrl = $router->generate('app_card_detail', ['numDemande' => $numDemande], UrlGeneratorInterface::ABSOLUTE_URL);

            $qrCode = new QrCode($qrCodeUrl);
            $writer = new PngWriter();
            $qrCodeResult = $writer->write($qrCode);
            $qrCodeDataUri = 'data:image/png;base64,' . base64_encode($qrCodeResult->getString());

            $professionnel = $carte->getProfessionnel();
            $profession = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);

            $html = $this->renderView('impression/card.html.twig', [
                'carte' => $carte,
                'profession' => $profession,
                'qrCode' => $qrCodeDataUri,
                'president'=> $presidentActuel,
            ]);

            $options = [
                'page-width' => '85.60mm',
                'page-height' => '53.98mm',
                'margin-top' => 0,
                'margin-right' => 0,
                'margin-bottom' => 0,
                'margin-left' => 0,
                'disable-local-file-access' => true,
            ];

            try {
                $pdfContent = $pdf->getOutputFromHtml($html, $options);
            } catch (\Exception $e) {
                return new Response(
                    'Error generating PDF: ' . $e->getMessage(),
                    500
                );
            }

            // Retourne le PDF directement pour prévisualisation sans sauvegarde
            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview-carte.pdf"',
            ]);
        }
    */

    #[Route('/impression/apercu/{id}', name: 'app_impression_apercu')]
    public function impressionApercu(Carte $carte): Response
    {
        return $this->render('impression/impressionApercu.html.twig', [
            'carte' => $carte,
        ]);
    }

    #[Route('/card/detail/{numDemande?}', name: 'app_card_detail')]
    public function cardRequestDetail(?string $numDemande, DemandeRepository $demandeRepository): Response
    {
        // Vérifier si le numDemande est fourni
        if (is_null($numDemande)) {
            // Retourner une vue avec un message d'erreur si numDemande est vide
            return $this->render('impression/request_detail.html.twig', [
                'carte' => null,
                'numDemande' => null,
                'error' => 'Le numéro de demande n\'a pas été fourni.'
            ]);
        }

        $numDemande = str_replace('-', '/', $numDemande);

        // Rechercher la demande à partir du numéro de demande
        $demande = $demandeRepository->findOneBy(['numDemande' => $numDemande]);

        // Vérifier si la demande existe
        if (!$demande) {
            // Retourner une vue avec un message d'erreur si la demande n'existe pas
            return $this->render('impression/request_detail.html.twig', [
                'carte' => null,
                'numDemande' => $numDemande,
                'error' => 'La demande avec le numéro fourni est introuvable.'
            ]);
        }

        // Retourner une vue avec les détails de la demande de carte
        return $this->render('impression/request_detail.html.twig', [
            'carte' => $demande,
            'numDemande' => $numDemande,
            'error' => null
        ]);
    }
    #[Route('/cartes-imprimees', name: 'app_printed_cards')]
    public function printedCards(DemandeRepository $demandeRepository): Response
    {
        // Récupérer toutes les demandes dont isPrinted est à 1 et urlFile n'est pas null
        $cartes = $demandeRepository->findBy([
            'isPrinted' => 1
        ]);

        // Séparer les cartes valides et expirées
        $cartesValides = [];
        $cartesExpirees = [];

        foreach ($cartes as $carte) {
            if ($carte->getDateExpiration() > new \DateTime()) {
                $cartesValides[] = $carte;
            } else {
                $cartesExpirees[] = $carte;
            }
        }



        return $this->render('impression/printed_cards.html.twig', [
            'cartesValides' => $cartesValides,
            'cartesExpirees' => $cartesExpirees,
        ]);
    }

}
