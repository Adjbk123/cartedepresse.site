<?php

namespace App\Controller;

use App\Entity\TypeOrgane;
use App\Form\TypeOrganeType;
use App\Repository\TypeOrganeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/type/organe')]
class TypeOrganeController extends AbstractController
{
    #[Route('/', name: 'app_type_organe_index', methods: ['GET'])]
    public function index(TypeOrganeRepository $typeOrganeRepository): Response
    {
        return $this->render('type_organe/index.html.twig', [
            'type_organes' => $typeOrganeRepository->findAll(),
        ]);
    }

    #[Route('/creation', name: 'app_type_organe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $typeOrgane = new TypeOrgane();
        $form = $this->createForm(TypeOrganeType::class, $typeOrgane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeOrgane);
            $entityManager->flush();

            return $this->redirectToRoute('app_type_organe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_organe/new.html.twig', [
            'type_organe' => $typeOrgane,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_organe_show', methods: ['GET'])]
    public function show(TypeOrgane $typeOrgane): Response
    {
        return $this->render('type_organe/show.html.twig', [
            'type_organe' => $typeOrgane,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_organe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeOrgane $typeOrgane, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TypeOrganeType::class, $typeOrgane);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_type_organe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('type_organe/edit.html.twig', [
            'type_organe' => $typeOrgane,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_organe_delete', methods: ['POST'])]
    public function delete(Request $request, TypeOrgane $typeOrgane, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeOrgane->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeOrgane);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_type_organe_index', [], Response::HTTP_SEE_OTHER);
    }
}
