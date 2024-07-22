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



    #[Route('/print/card/{id}', name: 'app_print_card')]
    public function printCard(Demande $carte, PresidentRepository $presidentRepository, Pdf $pdf, EntityManagerInterface $entityManager, HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository, UrlGeneratorInterface $router): Response
    {
        if (!$carte) {
            throw $this->createNotFoundException('La carte demandée n\'existe pas.');
        }

        $carte->setDateDelivrance(new \DateTime());
        // Ajouter 3 ans à la date de délivrance pour obtenir la date d'expiration
        $dateDelivrance = $carte->getDateDelivrance();
        $dateExpiration = (clone $dateDelivrance)->modify('+3 years');

        // Définir la date d'expiration sur l'entité
        $carte->setDateExpiration($dateExpiration);
        $entityManager->flush();

        $presidentActuel = $presidentRepository->findOneBy(['isPresident' => 1]);

        $numDemande = str_replace('/', '-', $carte->getNumDemande());

        // Générer l'URL avec le numéro de demande
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

        // Nomdu fichier PDF
        $filename = sprintf('carte-%s.pdf', $carte->getNumDemande());

        // Configuration des dimensions du document en A7 (74mm x 105mm)
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
        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
            ]
        );
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

}
