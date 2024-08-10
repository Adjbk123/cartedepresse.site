<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\DuplicataDemande;
use App\Form\DuplicataDemandeType;
use App\Repository\DemandeRepository;
use App\Repository\DuplicataDemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/duplicata/demande')]
class DuplicataDemandeController extends AbstractController
{
    #[Route('/', name: 'app_duplicata_demande_index', methods: ['GET'])]
    public function index(DuplicataDemandeRepository $duplicataDemandeRepository): Response
    {
        return $this->render('duplicata_demande/index.html.twig', [
            'duplicatas' => $duplicataDemandeRepository->findBy(['statut'=>"En attente"]),
        ]);
    }

    #[Route('/valide', name: 'app_duplicata_demande_validee', methods: ['GET'])]
    public function validee(DuplicataDemandeRepository $duplicataDemandeRepository): Response
    {
        $validatedDuplicatas =  $duplicataDemandeRepository->findBy(['statut'=>"Validée"]);
        return $this->render('duplicata_demande/indexValidee.html.twig', [
            'duplicatas' => $validatedDuplicatas,
        ]);
    }

    #[Route('/rejetee', name: 'app_duplicata_demande_rejetee', methods: ['GET'])]
    public function rejetee(DuplicataDemandeRepository $duplicataDemandeRepository): Response
    {
        $rejectedDuplicatas = $duplicataDemandeRepository->findBy(['statut'=>"Rejetée"]);

        return $this->render('duplicata_demande/indexRejetee.html.twig', [
            'duplicatas' => $rejectedDuplicatas,
        ]);
    }
    #[Route('/new', name: 'app_duplicata_demande_new')]
    public function demandeDuplicata(Request $request,DemandeRepository $carteRepository, EntityManagerInterface $em): Response
    {
        $professionnel = $this->getUser()->getId();

        $carte = $carteRepository->findValidCardForProfessionnel($professionnel);

        if (!$carte) {
            $this->addFlash('error', 'Vous n\'avez pas de carte valide pour demander un duplicata.');
            return $this->redirectToRoute('app_accueil');
        }

        $duplicataDemande = new DuplicataDemande();

        $duplicataDemande->setStatut('En attente');
        $duplicataDemande->setDateDemande(new \DateTime());
        $duplicataDemande->setDemande($carte);

        $form = $this->createForm(DuplicataDemandeType::class, $duplicataDemande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileDeclaration = $form->get('declarationPerte')->getData();
            $fileCip = $form->get('cip')->getData();

            $directory = 'uploads/duplicata_demande';

            // Enregistrement des fichiers avec un nom unique
            $filenameDeclaration = uniqid().'.'.$fileDeclaration->guessExtension();
            $filenameCip = uniqid().'.'.$fileCip->guessExtension();

            $fileDeclaration->move($directory, $filenameDeclaration);
            $fileCip->move($directory, $filenameCip);

            $duplicataDemande->setDeclarationPerte($filenameDeclaration);
            $duplicataDemande->setCip($filenameCip);

            $em->persist($duplicataDemande);
            $em->flush();

            $this->addFlash('success', 'Votre demande de duplicata a été soumise avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('duplicata_demande/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/valider/{id}', name: 'valider_duplicata')]
    public function validerDuplicata(DuplicataDemande $duplicataDemande, EntityManagerInterface $em): Response
    {
        $duplicataDemande->setStatut('Validée');
        $em->flush();

        $this->addFlash('success', 'La demande a été validée.');
        return $this->redirectToRoute('app_duplicata_demande_index');
    }

    #[Route('/rejeter/{id}', name: 'rejeter_duplicata')]
    public function rejeterDuplicata(DuplicataDemande $duplicataDemande, EntityManagerInterface $em): Response
    {
        $duplicataDemande->setStatut('Rejetée');
        $em->flush();

        $this->addFlash('error', 'La demande a été rejetée.');
        return $this->redirectToRoute('app_duplicata_demande_index');
    }

    #[Route('/{id}', name: 'app_duplicata_demande_show', methods: ['GET'])]
    public function show(DuplicataDemande $duplicataDemande): Response
    {
        return $this->render('duplicata_demande/show.html.twig', [
            'duplicata_demande' => $duplicataDemande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_duplicata_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DuplicataDemande $duplicataDemande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DuplicataDemandeType::class, $duplicataDemande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_duplicata_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('duplicata_demande/edit.html.twig', [
            'duplicata_demande' => $duplicataDemande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_duplicata_demande_delete', methods: ['POST'])]
    public function delete(Request $request, DuplicataDemande $duplicataDemande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$duplicataDemande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($duplicataDemande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_duplicata_demande_index', [], Response::HTTP_SEE_OTHER);
    }
}
