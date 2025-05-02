<?php

namespace App\Controller;

use App\Entity\HistoriqueOrganeProfessionnel;
use App\Entity\Organe;
use App\Entity\Profession;
use App\Entity\User;
use App\Form\PersonnelType;
use App\Form\UserType;
use App\Repository\OrganeRepository;
use App\Repository\ProfessionRepository;
use App\Repository\UserRepository;
use App\Service\EmailNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    #[IsGranted("ROLE_USER")]
  #[Route('/professionnel', name: 'app_user_professionnel', methods: ['GET'])]
    public function indexProfessionnel(UserRepository $userRepository): Response
    {
        $professionnels = $userRepository->findByRole('ROLE_PROFESSIONNEL');
        return $this->render('user/professionnel.html.twig', [
            'users' => $professionnels,
        ]);
    }
    #[IsGranted("ROLE_USER")]
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
    #[IsGranted("ROLE_USER")]
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[IsGranted("ROLE_USER")]
    #[Route('/{id}/modifier-compte', name: 'app_professionnel_edit', methods: ['GET', 'POST'])]
    public function editProfessionnel(Request $request, User $user, EntityManagerInterface $entityManager, UserRepository $userRepository, SluggerInterface $slugger, OrganeRepository $organeRepository, ProfessionRepository $professionRepository): Response
    {
        if ($user !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier les informations de ce compte');
            return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
        }

        if ($request->isMethod('POST')) {
            $user->setPrenoms($request->request->get('prenoms'));
            $user->setNom($request->request->get('nom'));
            $user->setTelephone($request->request->get('telephone'));
            $user->setDateNaissance(new \DateTime($request->request->get('dateNaissance')));
            $user->setLieuNaissance($request->request->get('lieuNaissance'));
            $user->setNpi($request->request->get('npi'));
            $user->setNationalite($request->request->get('nationalite'));
            $user->setSexe($request->request->get('sexe'));

            $croppedPhoto = $request->files->get('photo');

            if ($croppedPhoto && $croppedPhoto->isValid()) {
                $repertoire = 'uploads';

                if (!file_exists($repertoire)) {
                    mkdir($repertoire, 0777, true);
                }

                $nomCompletUtilisateur = $user->getNom() . '-' . $user->getPrenoms();
                $date = (new \DateTime())->format('Ymd-His');
                $nomFichierSecurise = $slugger->slug($nomCompletUtilisateur . '-' . $date);

                $extension = $croppedPhoto->guessExtension();
                $nouveauNomFichier = $nomFichierSecurise . '.' . $extension;

                $croppedPhoto->move($repertoire, $nouveauNomFichier);

                $user->setPhoto($nouveauNomFichier);
            }


            // Gestion de l'organe
            if ($request->request->get('organe') === 'autre') {
                $nouvelOrgane = new Organe();
                $nouvelOrgane->setDesignation($request->request->get('nouvelOrganeNom'));
                $nouvelOrgane->setActif(true);
                $entityManager->persist($nouvelOrgane);
                $entityManager->flush();
                $organe = $nouvelOrgane;
            } else {
                $organe = $entityManager->getReference('App\Entity\Organe', $request->request->get('organe'));
            }

            // Gestion de la profession
            if ($request->request->get('profession') === 'autre') {
                $nouvelleProfession = new Profession();
                $nouvelleProfession->setLibelle($request->request->get('nouvelleProfessionNom'));
                $entityManager->persist($nouvelleProfession);
                $entityManager->flush();
                $profession = $nouvelleProfession;
            } else {
                $profession = $entityManager->getReference('App\Entity\Profession', $request->request->get('profession'));
            }

            $historiqueOrganeProfessionnel = new HistoriqueOrganeProfessionnel();
            $historiqueOrganeProfessionnel->setProfession($profession);
            $historiqueOrganeProfessionnel->setProfessionnel($user);
            $historiqueOrganeProfessionnel->setOrgane($organe);
            $entityManager->persist($historiqueOrganeProfessionnel);

            $entityManager->flush();

            return $this->redirectToRoute('app_accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'nationalities' => $this->getPays(), // Remplacez par votre liste de nationalités
            'organes' => $organeRepository->findBy(['isActif' => true], ['designation' => 'ASC']),
            'professions' => $professionRepository->findBy([], ['libelle'=>'ASC']),
        ]);
    }


    public function getPays()
    {
        // Tableau des nationalités
        $nationalites =  [
            'Afghan(e)' => 'Afghan(e)',
            'Albanais(e)' => 'Albanais(e)',
            'Algérien(ne)' => 'Algérien(ne)',
            'Américain(e)' => 'Américain(e)',
            'Andorran(e)' => 'Andorran(e)',
            'Angolais(e)' => 'Angolais(e)',
            'Antiguais(e)' => 'Antiguais(e)',
            'Argentin(e)' => 'Argentin(e)',
            'Arménien(ne)' => 'Arménien(ne)',
            'Australien(ne)' => 'Australien(ne)',
            'Autrichien(ne)' => 'Autrichien(ne)',
            'Azerbaïdjanais(e)' => 'Azerbaïdjanais(e)',
            'Bahreïnien(ne)' => 'Bahreïnien(ne)',
            'Bangladais(e)' => 'Bangladais(e)',
            'Barbadien(ne)' => 'Barbadien(ne)',
            'Bélizien(ne)' => 'Bélizien(ne)',
            'Béninois(e)' => 'Béninois(e)',
            'Bhoutanais(e)' => 'Bhoutanais(e)',
            'Biélorusse' => 'Biélorusse',
            'Birman(e)' => 'Birman(e)',
            'Bissau-Guinéen(ne)' => 'Bissau-Guinéen(ne)',
            'Bolivien(ne)' => 'Bolivien(ne)',
            'Bosnien(ne)' => 'Bosnien(ne)',
            'Botswanais(e)' => 'Botswanais(e)',
            'Brésilien(ne)' => 'Brésilien(ne)',
            'Britannique' => 'Britannique',
            'Brunéien(ne)' => 'Brunéien(ne)',
            'Bulgare' => 'Bulgare',
            'Burkinabé' => 'Burkinabé',
            'Burundais(e)' => 'Burundais(e)',
            'Cambodgien(ne)' => 'Cambodgien(ne)',
            'Camerounais(e)' => 'Camerounais(e)',
            'Canadien(ne)' => 'Canadien(ne)',
            'Cap-Verdien(ne)' => 'Cap-Verdien(ne)',
            'Centrafricain(e)' => 'Centrafricain(e)',
            'Chilien(ne)' => 'Chilien(ne)',
            'Chinois(e)' => 'Chinois(e)',
            'Colombien(ne)' => 'Colombien(ne)',
            'Comorien(ne)' => 'Comorien(ne)',
            'Congolais(e)' => 'Congolais(e)',
            'Costaricien(ne)' => 'Costaricien(ne)',
            'Croate' => 'Croate',
            'Cubain(e)' => 'Cubain(e)',
            'Cypriote' => 'Cypriote',
            'Danois(e)' => 'Danois(e)',
            'Djiboutien(ne)' => 'Djiboutien(ne)',
            'Dominiquais(e)' => 'Dominiquais(e)',
            'Égyptien(ne)' => 'Égyptien(ne)',
            'Émirati(e)' => 'Émirati(e)',
            'Équatorien(ne)' => 'Équatorien(ne)',
            'Érythréen(ne)' => 'Érythréen(ne)',
            'Espagnol(e)' => 'Espagnol(e)',
            'Estonien(ne)' => 'Estonien(ne)',
            'Éthiopien(ne)' => 'Éthiopien(ne)',
            'Fidjien(ne)' => 'Fidjien(ne)',
            'Finlandais(e)' => 'Finlandais(e)',
            'Français(e)' => 'Français(e)',
            'Gabonais(e)' => 'Gabonais(e)',
            'Gambien(ne)' => 'Gambien(ne)',
            'Géorgien(ne)' => 'Géorgien(ne)',
            'Ghanéen(ne)' => 'Ghanéen(ne)',
            'Grec(que)' => 'Grec(que)',
            'Grenadien(ne)' => 'Grenadien(ne)',
            'Guatémaltèque' => 'Guatémaltèque',
            'Guinéen(ne)' => 'Guinéen(ne)',
            'Guinéen(ne)-Bissau' => 'Guinéen(ne)-Bissau',
            'Guyanien(ne)' => 'Guyanien(ne)',
            'Haïtien(ne)' => 'Haïtien(ne)',
            'Hondurien(ne)' => 'Hondurien(ne)',
            'Hongrois(e)' => 'Hongrois(e)',
            'Indien(ne)' => 'Indien(ne)',
            'Indonésien(ne)' => 'Indonésien(ne)',
            'Irakien(ne)' => 'Irakien(ne)',
            'Irlandais(e)' => 'Irlandais(e)',
            'Islandais(e)' => 'Islandais(e)',
            'Israélien(ne)' => 'Israélien(ne)',
            'Italien(ne)' => 'Italien(ne)',
            'Ivoirien(ne)' => 'Ivoirien(ne)',
            'Jamaïcain(e)' => 'Jamaïcain(e)',
            'Japonais(e)' => 'Japonais(e)',
            'Jordanien(ne)' => 'Jordanien(ne)',
            'Kazakh(e)' => 'Kazakh(e)',
            'Kenyan(e)' => 'Kenyan(e)',
            'Kirghiz(e)' => 'Kirghiz(e)',
            'Kiribatien(ne)' => 'Kiribatien(ne)',
            'Kittitien(ne)' => 'Kittitien(ne)',
            'Koweïtien(ne)' => 'Koweïtien(ne)',
            'Laotien(ne)' => 'Laotien(ne)',
            'Letton(ne)' => 'Letton(ne)',
            'Libanais(e)' => 'Libanais(e)',
            'Libérien(ne)' => 'Libérien(ne)',
            'Libyen(ne)' => 'Libyen(ne)',
            'Liechtensteinois(e)' => 'Liechtensteinois(e)',
            'Lituanien(ne)' => 'Lituanien(ne)',
            'Luxembourgeois(e)' => 'Luxembourgeois(e)',
            'Macédonien(ne)' => 'Macédonien(ne)',
            'Malaisien(ne)' => 'Malaisien(ne)',
            'Maldivien(ne)' => 'Maldivien(ne)',
            'Malien(ne)' => 'Malien(ne)',
            'Maltais(e)' => 'Maltais(e)',
            'Maréchalien(ne)' => 'Maréchalien(ne)',
            'Marocain(e)' => 'Marocain(e)',
            'Mauricien(ne)' => 'Mauricien(ne)',
            'Mauritanien(ne)' => 'Mauritanien(ne)',
            'Mexicain(e)' => 'Mexicain(e)',
            'Micronésien(ne)' => 'Micronésien(ne)',
            'Moldave' => 'Moldave',
            'Monégasque' => 'Monégasque',
            'Mongol(e)' => 'Mongol(e)',
            'Monténégrin(e)' => 'Monténégrin(e)',
            'Mozambicain(e)' => 'Mozambicain(e)',
            'Namibien(ne)' => 'Namibien(ne)',
            'Nauruan(e)' => 'Nauruan(e)',
            'Néerlandais(e)' => 'Néerlandais(e)',
            'Néo-Zélandais(e)' => 'Néo-Zélandais(e)',
            'Népalais(e)' => 'Népalais(e)',
            'Nicaraguayen(ne)' => 'Nicaraguayen(ne)',
            'Nigérian(e)' => 'Nigérian(e)',
            'Nigerien(ne)' => 'Nigerien(ne)',
            'Nord-Coréen(ne)' => 'Nord-Coréen(ne)',
            'Norvégien(ne)' => 'Norvégien(ne)',
            'Omanien(ne)' => 'Omanien(ne)',
            'Ougandais(e)' => 'Ougandais(e)',
            'Ouzbek(e)' => 'Ouzbek(e)',
            'Pakistanais(e)' => 'Pakistanais(e)',
            'Palestinien(ne)' => 'Palestinien(ne)',
            'Panaméen(ne)' => 'Panaméen(ne)',
            'Papouasien(ne)' => 'Papouasien(ne)',
            'Paraguayen(ne)' => 'Paraguayen(ne)',
            'Péruvien(ne)' => 'Péruvien(ne)',
            'Philippin(e)' => 'Philippin(e)',
            'Polonais(e)' => 'Polonais(e)',
            'Portoricain(e)' => 'Portoricain(e)',
            'Portugais(e)' => 'Portugais(e)',
            'Qatarien(ne)' => 'Qatarien(ne)',
            'Roumain(e)' => 'Roumain(e)',
            'Russe' => 'Russe',
            'Rwandais(e)' => 'Rwandais(e)',
            'Saint-Lucien(ne)' => 'Saint-Lucien(ne)',
            'Salomonais(e)' => 'Salomonais(e)',
            'Salvadorien(ne)' => 'Salvadorien(ne)',
            'Samoan(e)' => 'Samoan(e)',
            'Santoméen(ne)' => 'Santoméen(ne)',
            'Saoudien(ne)' => 'Saoudien(ne)',
            'Sénégalais(e)' => 'Sénégalais(e)',
            'Serbe' => 'Serbe',
            'Seychellois(e)' => 'Seychellois(e)',
            'Sierra-Léonais(e)' => 'Sierra-Léonais(e)',
            'Singapourien(ne)' => 'Singapourien(ne)',
            'Slovaque' => 'Slovaque',
            'Slovène' => 'Slovène',
            'Somalien(ne)' => 'Somalien(ne)',
            'Soudanais(e)' => 'Soudanais(e)',
            'Sri-Lankais(e)' => 'Sri-Lankais(e)',
            'Sud-Africain(e)' => 'Sud-Africain(e)',
            'Sud-Coréen(ne)' => 'Sud-Coréen(ne)',
            'Sud-Soudanais(e)' => 'Sud-Soudanais(e)',
            'Suédois(e)' => 'Suédois(e)',
            'Suisse' => 'Suisse',
            'Surinamien(ne)' => 'Surinamien(ne)',
            'Swazi(e)' => 'Swazi(e)',
            'Syrien(ne)' => 'Syrien(ne)',
            'Tadjik(e)' => 'Tadjik(e)',
            'Tanzanien(ne)' => 'Tanzanien(ne)',
            'Tchadien(ne)' => 'Tchadien(ne)',
            'Tchèque' => 'Tchèque',
            'Thaïlandais(e)' => 'Thaïlandais(e)',
            'Timorais(e)' => 'Timorais(e)',
            'Togolais(e)' => 'Togolais(e)',
            'Tongien(ne)' => 'Tongien(ne)',
            'Trinidadien(ne)' => 'Trinidadien(ne)',
            'Tunisien(ne)' => 'Tunisien(ne)',
            'Turkmène' => 'Turkmène',
            'Turc(que)' => 'Turc(que)',
            'Tuvaluan(e)' => 'Tuvaluan(e)',
            'Ukrainien(ne)' => 'Ukrainien(ne)',
            'Uruguayen(ne)' => 'Uruguayen(ne)',
            'Vanuatu(e)' => 'Vanuatu(e)',
            'Vaticanais(e)' => 'Vaticanais(e)',
            'Vénézuélien(ne)' => 'Vénézuélien(ne)',
            'Vietnamien(ne)' => 'Vietnamien(ne)',
            'Yéménite' => 'Yéménite',
            'Zambien(ne)' => 'Zambien(ne)',
            'Zimbabwéen(ne)' => 'Zimbabwéen(ne)',
        ];

        return $nationalites;

    }
    #[IsGranted("ROLE_USER")]
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
    #[IsGranted("ROLE_USER")]
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
