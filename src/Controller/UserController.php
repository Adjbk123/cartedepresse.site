<?php

namespace App\Controller;

use App\Entity\HistoriqueOrganeProfessionnel;
use App\Entity\User;
use App\Form\PersonnelType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\EmailNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
class UserController extends AbstractController
{

    private EmailNotificationService $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

  #[Route('/professionnel', name: 'app_user_professionnel', methods: ['GET'])]
    public function indexProfessionnel(UserRepository $userRepository): Response
    {
        $professionnels = $userRepository->findByRole('ROLE_PROFESSIONNEL');
        return $this->render('user/professionnel.html.twig', [
            'users' => $professionnels,
        ]);
    }
    #[Route('/professionnel/{id}', name: 'app_user_professionnel_show', methods: ['GET'])]
    public function indexProfessionnelShow(User $user): Response
    {
        return $this->render('user/professionnelDetail.html.twig', [
            'user' => $user,
        ]);
    }


    #[Route('/creer', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $typeCompte = $form->get('typeCompte')->getData();
            $user->setRoles([$typeCompte]);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/creer-personnel', name: 'app_personnel_new', methods: ['GET', 'POST'])]
    public function personnelNew(Request $request, EntityManagerInterface $entityManager , UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(PersonnelType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeCompte = $form->get('typeCompte')->getData();
            $user->setRoles([$typeCompte]);
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Récupérer le fichier téléchargé
            $fichier = $form['photo']->getData();
            if ($fichier != null) {
                $repertoire = "uploads";
                // Récupérer le nom original du fichier sans l'extension
                $nomOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // Récupérer l'extension du fichier
                $extension = $fichier->guessExtension();

                // Combiner le nom et le prénom de l'utilisateur
                $nomCompletUtilisateur = $user->getNom() . '_' . $user->getPrenoms();
                // Formater la date du jour
                $date = (new \DateTime())->format('Ymd_His');

                // Générer un nom de fichier sécurisé
                $nomFichierSecurise = $this->slugify($nomCompletUtilisateur . '_' . $date . '_' . $nomOriginal);
                $nouveauNomFichier = $nomFichierSecurise . '.' . $extension;

                // Déplacer le fichier vers le répertoire de destination
                $fichier->move($repertoire, $nouveauNomFichier);

                // Mettre à jour la photo de l'utilisateur
                $user->setPhoto($nouveauNomFichier);
            }
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setStatut('1');
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/personnelAdd.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/{id}/modifier-compte', name: 'app_professionnel_edit', methods: ['GET', 'POST'])]
    public function editProfessionnel(Request $request, User $user, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        if ($user != $this->getUser()) {
            $this->addFlash('error', 'Vous etes pas autorisé à modifier les informations de ce compte');
            return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer le fichier téléchargé
            $fichier = $form['photo']->getData();
            if ($fichier != null) {
                $repertoire = "uploads";
                // Récupérer le nom original du fichier sans l'extension
                $nomOriginal = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                // Récupérer l'extension du fichier
                $extension = $fichier->guessExtension();

                // Combiner le nom et le prénom de l'utilisateur
                $nomCompletUtilisateur = $user->getNom() . '_' . $user->getPrenoms();
                // Formater la date du jour
                $date = (new \DateTime())->format('Ymd_His');

                // Générer un nom de fichier sécurisé
                $nomFichierSecurise = $this->slugify($nomCompletUtilisateur . '_' . $date . '_' . $nomOriginal);
                $nouveauNomFichier = $nomFichierSecurise . '.' . $extension;

                // Déplacer le fichier vers le répertoire de destination
                $fichier->move($repertoire, $nouveauNomFichier);

                // Mettre à jour la photo de l'utilisateur
                $user->setPhoto($nouveauNomFichier);
            }

            $historiqueOrganeProfessionnel = new HistoriqueOrganeProfessionnel();

            $organe = $form['organe']->getData();
            $profession = $form['profession']->getData();
            $historiqueOrganeProfessionnel->setProfession($profession);
            $historiqueOrganeProfessionnel->setProfessionnel($user);
            $historiqueOrganeProfessionnel->setOrgane($organe);
            $entityManager->persist($historiqueOrganeProfessionnel);

            $entityManager->flush();

            return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


    private function slugify($text): string
    {
        // Replace non-letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // Trim
        $text = trim($text, '-');

        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // Lowercase
        $text = strtolower($text);

        return empty($text) ? 'n-a' : $text;
    }
}
