<?php

namespace App\Controller;

use App\Entity\HaacInfo;
use App\Form\HaacInfoType;
use App\Repository\HaacInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_COMITE_MEMBRE")'))]
#[Route('/haac/info')]
class HaacInfoController extends AbstractController
{
    #[Route('/', name: 'app_haac_info_index', methods: ['GET'])]
    public function index(HaacInfoRepository $haacInfoRepository): Response
    {
        return $this->render('haac_info/index.html.twig', [
            'haac_infos' => $haacInfoRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_haac_info_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $haacInfo = new HaacInfo();
        $form = $this->createForm(HaacInfoType::class, $haacInfo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadsDir = 'images';

            // logo HAAC
            $logoFile = $form->get('logoPath')->getData();
            if ($logoFile) {
                $logoFilename = uniqid() . '.' . $logoFile->guessExtension();
                $logoFile->move($uploadsDir, $logoFilename);
                $haacInfo->setLogoPath('images/' . $logoFilename);
            }

            // couleurs nationales (logo Bénin)
            $logoBeninFile = $form->get('logoBeninPath')->getData();
            if ($logoBeninFile) {
                $logoBeninFilename = uniqid() . '.' . $logoBeninFile->guessExtension();
                $logoBeninFile->move($uploadsDir, $logoBeninFilename);
                $haacInfo->setLogoBeninPath('images/' . $logoBeninFilename);
            }

            // armoiries
            $amoirieFile = $form->get('amoiriePath')->getData();
            if ($amoirieFile) {
                $amoirieFilename = uniqid() . '.' . $amoirieFile->guessExtension();
                $amoirieFile->move($uploadsDir, $amoirieFilename);
                $haacInfo->setAmoiriePath('images/' . $amoirieFilename);
            }

            // cachet
            $cachetFile = $form->get('cachetPath')->getData();
            if ($cachetFile) {
                $cachetFilename = uniqid() . '.' . $cachetFile->guessExtension();
                $cachetFile->move($uploadsDir, $cachetFilename);
                $haacInfo->setCachetPath('images/' . $cachetFilename);
            }

            $entityManager->persist($haacInfo);
            $entityManager->flush();

            return $this->redirectToRoute('app_haac_info_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('haac_info/new.html.twig', [
            'haac_info' => $haacInfo,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_haac_info_show', methods: ['GET'])]
    public function show(HaacInfo $haacInfo): Response
    {
        return $this->render('haac_info/show.html.twig', [
            'haac_info' => $haacInfo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_haac_info_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HaacInfo $haacInfo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HaacInfoType::class, $haacInfo);
        $form->handleRequest($request);

        // Sauvegarde des chemins d'origine
        $oldLogoPath = $haacInfo->getLogoPath();
        $oldLogoBeninPath = $haacInfo->getLogoBeninPath();
        $oldAmoiriePath = $haacInfo->getAmoiriePath();
        $oldCachetPath = $haacInfo->getCachetPath();

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadsDir ='images';

            // logo HAAC
            $logoFile = $form->get('logoPath')->getData();
            if ($logoFile) {
                $logoFilename = uniqid() . '.' . $logoFile->guessExtension();
                $logoFile->move($uploadsDir, $logoFilename);
                $haacInfo->setLogoPath('images/' . $logoFilename);
            } else {
                $haacInfo->setLogoPath($oldLogoPath);
            }

            // couleurs nationales
            $logoBeninFile = $form->get('logoBeninPath')->getData();
            if ($logoBeninFile) {
                $logoBeninFilename = uniqid() . '.' . $logoBeninFile->guessExtension();
                $logoBeninFile->move($uploadsDir, $logoBeninFilename);
                $haacInfo->setLogoBeninPath('images/' . $logoBeninFilename);
            } else {
                $haacInfo->setLogoBeninPath($oldLogoBeninPath);
            }

            // armoiries
            $amoirieFile = $form->get('amoiriePath')->getData();
            if ($amoirieFile) {
                $amoirieFilename = uniqid() . '.' . $amoirieFile->guessExtension();
                $amoirieFile->move($uploadsDir, $amoirieFilename);
                $haacInfo->setAmoiriePath('images/' . $amoirieFilename);
            } else {
                $haacInfo->setAmoiriePath($oldAmoiriePath);
            }

            // cachet
            $cachetFile = $form->get('cachetPath')->getData();
            if ($cachetFile) {
                $cachetFilename = uniqid() . '.' . $cachetFile->guessExtension();
                $cachetFile->move($uploadsDir, $cachetFilename);
                $haacInfo->setCachetPath('images/' . $cachetFilename);
            } else {
                $haacInfo->setCachetPath($oldCachetPath);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_haac_info_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('haac_info/edit.html.twig', [
            'haac_info' => $haacInfo,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_haac_info_delete', methods: ['POST'])]
    public function delete(Request $request, HaacInfo $haacInfo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$haacInfo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($haacInfo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_haac_info_index', [], Response::HTTP_SEE_OTHER);
    }
}
