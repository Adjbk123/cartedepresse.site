<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\PresidentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Knp\Snappy\Pdf;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImpressionController extends AbstractController
{
    #[Route('/impression', name: 'app_impression')]
    public function index(DemandeRepository $demandeRepository): Response
    {

        $cartePretes = $demandeRepository->findBy(['isReadyForPrint'=>1, 'dateDelivrance'=> null, "dateExpiration"=>null]);

        return $this->render('impression/index.html.twig', [
           'cartePretes' => $cartePretes,
        ]);
    }



    #[Route('/print/card/save/{id}', name: 'app_print_card')]
    public function saveCard(Demande $carte,
                             PresidentRepository $presidentRepository,
                             Pdf $pdf, EntityManagerInterface $entityManager,
                             HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
                             UrlGeneratorInterface $router, ): Response
    {
        $uploadDir = $this->getParameter('uploadDir');

        if (!$carte) {
            throw $this->createNotFoundException('La carte demandée n\'existe pas.');
        }

        $carte->setDateDelivrance(new \DateTime());
        $dateDelivrance = $carte->getDateDelivrance();
        $dateExpiration = (clone $dateDelivrance)->modify('+3 years');
        $carte->setDateExpiration($dateExpiration);
        $entityManager->flush();

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

        $filename = sprintf('carte-%s.pdf',$numDemande);

        $carte->setPrinted(1);
        $carte->setUrlFile($filename);
        $entityManager->flush();

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


        // Sauvegarde du fichier PDF dans un dossier
        $filePath = $uploadDir . '/' . $filename;
        file_put_contents($filePath, $pdfContent);

        return $this->redirectToRoute('app_impression_apercu',['id'=>$carte->getId()]);
    }

    #[Route('/impression/apercu/{id}', name: 'app_impression_apercu')]
    public function impressionApercu(Demande $demande): Response
    {


        return $this->render('impression/impressionApercu.html.twig', [
            'demande' => $demande,
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
