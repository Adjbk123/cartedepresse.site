<?php

namespace App\Controller;

use App\Entity\President;
use App\Form\PresidentType;
use App\Repository\PresidentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]

#[Route('/president')]
class PresidentController extends AbstractController
{
    #[Route('/', name: 'app_president_index', methods: ['GET'])]
    public function index(PresidentRepository $presidentRepository): Response
    {
        return $this->render('president/index.html.twig', [
            'presidents' => $presidentRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_president_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $president = new President();
        $form = $this->createForm(PresidentType::class, $president);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Désactiver l'ancien président
            $dernierPresident = $entityManager->getRepository(President::class)
                ->findOneBy(['isPresident' => true]);

            if ($dernierPresident) {
                $dernierPresident->setPresident(false);
                $entityManager->persist($dernierPresident);
            }

            // Activer le nouveau président
            $president->setPresident(true);

            // Gestion du fichier de signature
            $fileSignature = $form['signature']->getData();
            $directory = "uploads";

            if ($fileSignature) {
                $nomFichier = $fileSignature->getClientOriginalName();
                $fileSignature->move($directory, $nomFichier);
                $president->setSignature($directory.'/'.$nomFichier);
            }

            $entityManager->persist($president);
            $entityManager->flush();

            return $this->redirectToRoute('app_president_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('president/new.html.twig', [
            'president' => $president,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_president_show', methods: ['GET'])]
    public function show(President $president): Response
    {
        return $this->render('president/show.html.twig', [
            'president' => $president,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_president_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, President $president, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PresidentType::class, $president);
        $form->handleRequest($request);

        // Sauvegarder l'ancienne signature si besoin
        $ancienneSignature = $president->getSignature();

        if ($form->isSubmitted() && $form->isValid()) {


            $fileSignature = $form['signature']->getData();
            $directory = "uploads";

            if ($fileSignature) {
                $nomFichier = $fileSignature->getClientOriginalName();
                $fileSignature->move($directory, $nomFichier);
                $president->setSignature($directory.'/'.$nomFichier);

                // Optionnel : supprimer l'ancienne signature du disque si nécessaire
                if ($ancienneSignature && file_exists($ancienneSignature)) {
                    unlink($ancienneSignature);
                }
            } else {
                // Si aucun nouveau fichier, garder l’ancienne signature
                $president->setSignature($ancienneSignature);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_president_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('president/edit.html.twig', [
            'president' => $president,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_president_delete', methods: ['POST'])]
    public function delete(Request $request, President $president, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$president->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($president);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_president_index', [], Response::HTTP_SEE_OTHER);
    }
}
