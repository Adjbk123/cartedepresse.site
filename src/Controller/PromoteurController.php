<?php

namespace App\Controller;

use App\Entity\Promoteur;
use App\Form\PromoteurType;
use App\Repository\PromoteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route('/promoteur')]
class PromoteurController extends AbstractController
{
    #[Route('/', name: 'app_promoteur_index', methods: ['GET'])]
    public function index(PromoteurRepository $promoteurRepository): Response
    {
        return $this->render('promoteur/index.html.twig', [
            'promoteurs' => $promoteurRepository->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_promoteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $promoteur = new Promoteur();
        $form = $this->createForm(PromoteurType::class, $promoteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($promoteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_promoteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('promoteur/new.html.twig', [
            'promoteur' => $promoteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promoteur_show', methods: ['GET'])]
    public function show(Promoteur $promoteur): Response
    {
        return $this->render('promoteur/show.html.twig', [
            'promoteur' => $promoteur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_promoteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Promoteur $promoteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PromoteurType::class, $promoteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_promoteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('promoteur/edit.html.twig', [
            'promoteur' => $promoteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_promoteur_delete', methods: ['POST'])]
    public function delete(Request $request, Promoteur $promoteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$promoteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($promoteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_promoteur_index', [], Response::HTTP_SEE_OTHER);
    }
}
