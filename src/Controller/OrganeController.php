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


class OrganeController extends AbstractController
{
    #[Route('/liste-organe', name: 'app_organe_index', methods: ['GET'])]
    public function index(OrganeRepository $organeRepository): Response
    {
        return $this->render('organe/index.html.twig', [
            'organes' => $organeRepository->findAll(),
        ]);
    }

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

    #[Route('/organe/{id}', name: 'app_organe_show', methods: ['GET'])]
    public function show(Organe $organe): Response
    {
        return $this->render('organe/show.html.twig', [
            'organe' => $organe,
        ]);
    }

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
