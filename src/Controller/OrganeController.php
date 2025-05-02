<?php

namespace App\Controller;

use App\Entity\Organe;
use App\Form\OrganeType;
use App\Repository\OrganeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class OrganeController extends AbstractController
{
    #[IsGranted("ROLE_USER")]
    #[Route('/liste-organe', name: 'app_organe_index', methods: ['GET'])]
    public function index(OrganeRepository $organeRepository): Response
    {
        return $this->render('organe/index.html.twig', [
            'organes' => $organeRepository->findAll(),
        ]);
    }
    #[IsGranted("ROLE_USER")]
    #[Route('/organe/toggle/{id}', name: 'app_organe_toggle', methods: ['GET'])]
    public function toggleActivation(Organe $organe, EntityManagerInterface $entityManager): Response
    {
        $organe->setActif(!$organe->isActif());
        $entityManager->flush();

        $this->addFlash('success', sprintf('Organe %s avec succès.', $organe->isActif() ? 'activé' : 'désactivé'));

        return $this->redirectToRoute('app_organe_index');
    }
    #[IsGranted("ROLE_USER")]
    #[Route('/organe-nouveau', name: 'app_organe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organe = new Organe();
        $form = $this->createForm(OrganeType::class, $organe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organe);
            $entityManager->flush();

            return $this->redirectToRoute('app_organe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organe/new.html.twig', [
            'organe' => $organe,
            'form' => $form,
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/organe/{id}', name: 'app_organe_show', methods: ['GET'])]
    public function show(Organe $organe): Response
    {
        return $this->render('organe/show.html.twig', [
            'organe' => $organe,
        ]);
    }
    #[IsGranted("ROLE_USER")]
    #[Route('/organe/{id}/edit', name: 'app_organe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Organe $organe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrganeType::class, $organe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_organe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('organe/edit.html.twig', [
            'organe' => $organe,
            'form' => $form,
        ]);
    }
    #[IsGranted("ROLE_USER")]
    #[Route('/organe/{id}', name: 'app_organe_delete', methods: ['POST'])]
    public function delete(Request $request, Organe $organe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$organe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($organe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_organe_index', [], Response::HTTP_SEE_OTHER);
    }
}
