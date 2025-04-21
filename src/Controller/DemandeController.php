<?php

namespace App\Controller;

use App\Entity\Demande;
use App\Entity\HistoriqueOrganeProfessionnel;
use App\Entity\PieceJointe;
use App\Entity\PieceJointeAvisMembre;
use App\Entity\PieceJointeValidationStatut;
use App\Entity\User;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\HistoriqueOrganeProfessionnelRepository;
use App\Repository\OrganeRepository;
use App\Repository\PieceJointeRepository;
use App\Repository\ProfessionRepository;
use App\Repository\TypePieceRepository;
use App\Repository\UserRepository;
use App\Service\EmailNotificationService;
use App\Service\NumEnregistrementService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/demande')]
class DemandeController extends AbstractController
{
    private EmailNotificationService $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/', name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }


// Méthode pour afficher la page de suivi (GET)
    #[Route('/suivie', name: 'app_demande_suivi', methods: ['GET'])]
    public function afficherSuiviDemande(): Response
    {
        // Retourner la vue de la page de suivi de demande
        return $this->render('demande/indexSuiviDemande.html.twig');
    }

    // Méthode pour traiter la soumission du formulaire (POST via AJAX)
    #[Route('/verifier-demande', name: 'app_verifier_demande', methods: ['POST'])]
    public function verifierDemande(Request $request, DemandeRepository $demandeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $requestNumber = $data['request_number'];
        $email = $data['email'];

        // Vérifie si la demande existe
        $demande = $demandeRepository->findOneBy(['numDemande' => $requestNumber]);

        if (!$demande) {
            return $this->json(['status' => 'error', 'message' => 'Le numéro de demande n\'existe pas.']);
        }

        // Vérifie si l'email correspond
        if ($demande->getProfessionnel()->getEmail() !== $email) {
            return $this->json(['status' => 'error', 'message' => 'L\'adresse email ne correspond pas.']);
        }

        // Si tout est OK, renvoie les informations de la demande
        return $this->json([
            'status' => 'success',
            'demande' => [
                'statut' => $demande->getStatut(),
                'dateSoumission' => $demande->getDateSoumission()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/interne', name: 'app_demande_interne', methods: ['GET', 'POST'])]
    public function demandeInterne(
        Request $request,
        EntityManagerInterface $entityManager,
        DemandeRepository $demandeRepository,
        TypePieceRepository $typePieceRepository,
        OrganeRepository $organeRepository,
        ProfessionRepository $professionRepository,
        UserRepository $userRepository,
        NumEnregistrementService $numEnregistrementService
    ): Response
    {
        // Récupération des types de pièces, des organes et des professions
        $typePieces = $typePieceRepository->findBy(['typeDemande' => "Nouveau"]);
        $organes = $organeRepository->findAll();
        $professions = $professionRepository->findAll();

        $nationalites = $this->getPays();

        if ($request->isMethod('POST')) {
            // Traitement des données soumises
            $data = $request->request->all();

            // Créer un nouvel utilisateur
            $user = new User();
            $user->setNom($data['nom']);
            $user->setPrenoms($data['prenom']);
            $user->setEmail($data['email']);
            $user->setDateNaissance(new \DateTime($data['date_naissance']));
            $user->setLieuNaissance($data['lieu_naissance']);
            $user->setNpi($data['npi']);
            $user->setSexe($data['sexe']);
            $user->setNationalite($data['nationalite']);
            $user->setAdresse($data['adresse']);
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setStatut(1);
            $user->setRoles(['ROLE_PROFESSIONNEL']);
            $motdePasse= $data['password'];

            // Récupérer le fichier téléchargé
            $fichier = $request->files->get('photo');

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
                $nomFichierSecurise = $nomCompletUtilisateur . '_' . $date . '_' . $nomOriginal;
                $nouveauNomFichier = $nomFichierSecurise . '.' . $extension;

                // Déplacer le fichier vers le répertoire de destination
                $fichier->move($repertoire, $nouveauNomFichier);

                // Mettre à jour la photo de l'utilisateur
                $user->setPhoto($nouveauNomFichier);
            }

            $entityManager->persist($user);

            // Récupérer l'organe et la profession
            $organe = $organeRepository->find($data['organe']);
            $profession = $professionRepository->find($data['profession']);

            // Créer l'historique de l'organe et de la profession pour l'utilisateur
            $historiqueOrganeProfessionnel = new HistoriqueOrganeProfessionnel();
            $historiqueOrganeProfessionnel->setProfessionnel($user);
            $historiqueOrganeProfessionnel->setOrgane($organe);
            $historiqueOrganeProfessionnel->setProfession($profession);

            $entityManager->persist($historiqueOrganeProfessionnel);

            // Créer une nouvelle demande
            $demande = new Demande();
            $demande->setProfessionnel($user);
            $demande->setDateSoumission(new \DateTime());
            // Générer le numéro de demande aléatoire
            $numeroDemande = $this->genererNumeroAleatoire(6);
            $demande->setStatut('En attente');
            $demande->setNumDemande($numeroDemande);
            $demande->setHistoriqueOrganeActuel($historiqueOrganeProfessionnel);
            $demande->setTypeDemande("Etablissement");

            // Assurez-vous d'ajouter les autres champs nécessaires pour la demande

            $entityManager->persist($demande);

            $data = $request->files->get('typePieces');


            // Enregistrer les pièces jointes
            foreach ($data as $key => $pieceJointe) {
                $nomFichier = $pieceJointe->getClientOriginalName();
                $typePieceId = $key; // Supposant que $key est l'ID du type de pièce
                $typePiece = $typePieceRepository->find($typePieceId); // Récupérer l'entité TypePiece correspondante
                $dateSoumission = new \DateTime();
                $statut = 'En attente';
                $url = uniqid() . '_' . $user->getNom() . '_' . $user->getPrenoms() . '_' . $nomFichier;

                $directory = 'uploads/' . date('Y');
                $pieceJointe->move($directory, $url);

                $piece = new PieceJointe();
                $piece->setDemande($demande);
                $piece->setUrl($url);
                $piece->setTypePiece($typePiece);
                $piece->setDateSoumission($dateSoumission);
                $piece->setStatut($statut);

                $entityManager->persist($piece);
            }

            $this->emailNotificationService->sendAccountCreationNotification(
                $user->getNom(),
                $user->getPrenoms(),
                $user->getEmail(),
                $motdePasse,
                $numeroDemande
            );
            $entityManager->flush();

            // Redirection ou message de confirmation
            $this->addFlash('success', 'Demande enregistré avec succès');
            return $this->redirectToRoute('app_demande_en_attente');
        }

        // Affichage du formulaire
        return $this->render('demande/indexDemandeInterne.html.twig', [
            'typePieces' => $typePieces,
            'organes' => $organes,
            'professions' => $professions,
            'nationalites' => $nationalites,
        ]);
    }


    private function genererNumeroAleatoire(int $length): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
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
    #[Route('/nouvelle-demande', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        UserRepository $userRepository,
                        HistoriqueOrganeProfessionnelRepository $historiqueOrganeProfessionnelRepository,
                        TypePieceRepository $typePieceRepository,
                        NumEnregistrementService $numEnregistrementService,
                        EmailNotificationService $emailNotificationService
    ): Response {
        // Récupération du professionnel connecté
        $user = $this->getUser();
        $professionnel = $userRepository->findOneBy(['id' => $user]);

        // Vérifier si l'utilisateur a déjà une demande en cours
        $demandeEnCours = $entityManager->getRepository(Demande::class)->findOneBy([
            'professionnel' => $professionnel,
            'statut' => 'En attente'
        ]);

        if ($demandeEnCours) {
            $this->addFlash('error', 'Vous avez déjà une demande en cours.');
            return $this->redirectToRoute('app_espace');
        }

        // Type de pièce pour une nouvelle demande
        $typePiece = $typePieceRepository->findBy(['typeDemande' => "Nouveau"]);
        $demande = new Demande();

        if ($request->isMethod('POST')) {
            $data = $request->files->get('typePieces');
            // Générer le numéro de demande aléatoire
            $numeroDemande = $this->genererNumeroAleatoire(6);
            $demande->setNumDemande($numeroDemande);
            $demande->setStatut('En attente');
            $demande->setProfessionnel($professionnel);
            $demande->setDateSoumission(new \DateTime());
            $demande->setTypeDemande('Etablissement');

            $historiqueOrganeActuel = $historiqueOrganeProfessionnelRepository->findBy(['professionnel' => $professionnel], ['id' => 'DESC'], 1);
            $demande->setHistoriqueOrganeActuel($historiqueOrganeActuel[0]);

            // Enregistrer les pièces jointes
            foreach ($data as $key => $pieceJointe) {
                $nomFichier = $pieceJointe->getClientOriginalName();
                $typePieceId = $key; // Supposant que $key est l'ID du type de pièce
                $typePiece = $typePieceRepository->find($typePieceId); // Récupérer l'entité TypePiece correspondante
                $dateSoumission = new \DateTime();
                $statut = 'En attente';
                $url = uniqid() . '_' . $user->getNom() . '_' . $user->getPrenoms() . '_' . $nomFichier;

                $directory = 'uploads/' . date('Y');
                $pieceJointe->move($directory, $url);

                $piece = new PieceJointe();
                $piece->setDemande($demande);
                $piece->setUrl($url);
                $piece->setTypePiece($typePiece);
                $piece->setDateSoumission($dateSoumission);
                $piece->setStatut($statut);

                $entityManager->persist($piece);
            }

            $entityManager->persist($demande);
            $entityManager->flush();

            // Envoyer l'email de notification au professionnel
            $demandeData = [
                'nom' => $demande->getProfessionnel()->getNom() . " " . $demande->getProfessionnel()->getPrenoms(),
                'type' => "Demande d'établissement de carte",
                'dateSoumission' => $demande->getDateSoumission(),
                'statut' => $demande->getStatut(),
                'numeroDemande' => $numeroDemande,
                'lienSuiviDemande' => $this->generateUrl('app_demande_traiter', ['id' => $demande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
            $emailNotificationService->sendDemandSubmissionEmail($professionnel->getEmail(), $demandeData);

            // Envoyer l'email de notification aux membres du comité
            $comiteMembers = $userRepository->findByRole('ROLE_COMITE_MEMBRE');
            $comiteEmails = array_map(function($member) {
                return $member->getEmail();
            }, $comiteMembers);

            $emailNotificationService->sendNewDemandeNotification($comiteEmails, $demandeData);

            $this->addFlash('success', 'Votre demande a été soumise avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        // Récupération des historiques d'organe professionnel
        $historiqueOrganeProfessionnel = $historiqueOrganeProfessionnelRepository->findOneBy(['professionnel' => $professionnel]);

        if ($historiqueOrganeProfessionnel == null) {
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('demande/new.html.twig', [
            'demande' => $demande,
            'professionnel' => $professionnel,
            'historiqueOrganeProfessionnel' => $historiqueOrganeProfessionnel,
            "typePieces" => $typePiece,
        ]);
    }

    #[Route('/traitee', name: 'app_demande_traitee', methods: ['GET'])]
    public function indexDemandeTraitee(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexDemandeValidee.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"Validée"]),
        ]);
    }
    #[Route('/rejetee', name: 'app_demande_rejetee', methods: ['GET'])]
    public function indexDemandeRejetee(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexDemandeRejetee.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"Rejetée"]),
        ]);
    }

    #[Route('/{id}/piece-reupload', name: 'app_piece_reupload', methods: ['POST'])]
    public function reuploadPieceJointe(
        PieceJointe $pieceJointe,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Gestion de l'upload du nouveau fichier
        $fichier = $request->files->get('fichier');

        $user = $this->getUser();
        if ($fichier) {
            $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            $url = uniqid() . '_' . $user->getNom() . '_' . $user->getPrenoms() . '_' . $nomFichier;

            $directory = 'uploads/' . date('Y');
            $fichier->move($directory, $url);

            // Mettre à jour les informations de la pièce jointe
            $pieceJointe->setUrl($url);
            $pieceJointe->setDateSoumission(new \DateTime());
            $pieceJointe->setStatut('En attente');

            // Réinitialiser les compteurs et le statut de validation
            $statut = $entityManager->getRepository(PieceJointeValidationStatut::class)
                ->findOneBy(['piece' => $pieceJointe]);

            if ($statut) {
                $statut->setNbAvisTotal(0);
                $statut->setNbFavorable(0);
                $statut->setNbDefavorable(0);
                $statut->setStatutValidation('En attente');
                $entityManager->persist($statut);
            }

            // Marquer tous les avis associés à cette pièce comme inactifs
            $avis = $entityManager->getRepository(PieceJointeAvisMembre::class)
                ->findBy(['piece' => $pieceJointe]);

            foreach ($avis as $a) {
                $a->setActif(false);
                $entityManager->persist($a);
            }

            $entityManager->flush();
        }

        $this->addFlash('success', 'Votre pièce jointe a été rechargée avec succès.');
        return $this->redirectToRoute('app_accueil');
    }


    #[Route('/attente', name: 'app_demande_en_attente', methods: ['GET'])]
    public function indexAttente(DemandeRepository $demandeRepository): Response
    {
        return $this->render('demande/indexAttente.html.twig', [
            'demandes' => $demandeRepository->findBy(['statut'=>"En attente"]),
        ]);
    }

    #[Route('/{id}/traiter', name: 'app_demande_traiter', methods: ['GET'])]
    public function traiter(Demande $demande, PieceJointeRepository $pieceJointeRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $fichiers = $pieceJointeRepository->findBy(['demande' => $demande]);

        foreach ($fichiers as $fichier) {
            $userHasOpinion = false;
            $pieceReuploaded = $fichier->getStatut() === 'En attente';

            foreach ($fichier->getPieceJointeAvisMembres() as $avis) {
                if ($avis->getMembre() === $user) {
                    $userHasOpinion = true;
                    break;
                }
            }

            // Ajouter une propriété pour indiquer si l'utilisateur peut donner son avis
            $fichier->canGiveOpinion = !$userHasOpinion || $pieceReuploaded;
        }

        return $this->render('demande/traiter.html.twig', [
            'demande' => $demande,
            'fichiers' => $fichiers,
        ]);
    }


    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }
    #[Route('/{id}/fichier-valider', name: 'app_fichier_valider', methods: ['POST'])]
    public function validerFichier(
        PieceJointe $pieceJointe,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): RedirectResponse {
        if ($pieceJointe->getStatut() === 'Rejetée') {
            $this->addFlash('warning', 'Cette pièce a été rejetée. Veuillez la mettre à jour avant de la valider.');
            return $this->redirectToRoute('app_demande_traiter', ['id' => $pieceJointe->getDemande()->getId()]);
        }

        $user = $this->getUser();
        $justification = $request->request->get('observation');

        // Vérifier si l'utilisateur a déjà donné un avis
        $avisExistant = $entityManager->getRepository(PieceJointeAvisMembre::class)
            ->findOneBy(['piece' => $pieceJointe, 'membre' => $user]);

        if ($avisExistant) {
            $this->addFlash('warning', 'Vous avez déjà donné votre avis sur ce fichier.');
            return $this->redirectToRoute('app_demande_traiter', ['id' => $pieceJointe->getDemande()->getId()]);
        }

        // Créer un nouvel avis
        $avis = new PieceJointeAvisMembre();
        $avis->setPiece($pieceJointe);
        $avis->setMembre($user);
        $avis->setFavorable(true);
        $avis->setJustification($justification);
        $avis->setActif(1);
        $avis->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($avis);

        // Mettre à jour le suivi
        $statut = $entityManager->getRepository(PieceJointeValidationStatut::class)
            ->findOneBy(['piece' => $pieceJointe]);

        if (!$statut) {
            $statut = new PieceJointeValidationStatut();
            $statut->setPiece($pieceJointe);
            $statut->setNbAvisTotal(0);
            $statut->setNbFavorable(0);
        }

        $statut->setNbAvisTotal($statut->getNbAvisTotal() + 1);
        $statut->setNbFavorable($statut->getNbFavorable() + 1);

        $totalMembres = count($userRepository->findByRole('ROLE_COMITE_MEMBRE'));

        if ($totalMembres > 0) {
            $ratio = $statut->getNbFavorable() / $totalMembres;

            if ($ratio >= (2 / 3)) {
                $pieceJointe->setStatut('Validé');
                $statut->setStatutValidation('Validé');
            } else {
                $statut->setStatutValidation('En attente');
            }
        } else {
            $statut->setStatutValidation('En attente');
        }

        $entityManager->persist($statut);
        $entityManager->flush();

        // Vérifier si tous les fichiers de la demande sont validés
        $demande = $pieceJointe->getDemande();
        $tousFichiersValides = true;

        foreach ($demande->getPieceJointes() as $pj) {
            if ($pj->getStatut() !== 'Validé') {
                $tousFichiersValides = false;
                break;
            }
        }

        if ($tousFichiersValides) {
            $demande->setAgentTraitant($user);
            $demande->setDateTraitement(new \DateTime());
            $demande->setStatut('Validée');
            $entityManager->flush();
            $this->addFlash('success', 'Tous les fichiers ont été validés. La demande est maintenant validée.');
            return $this->redirectToRoute('app_demande_index');
        }

        $this->addFlash('success', 'Votre avis favorable a été enregistré.');
        return $this->redirectToRoute('app_demande_traiter', ['id' => $demande->getId()]);
    }

    #[Route('/{id}/fichier-rejeter', name: 'app_fichier_rejeter', methods: ['POST'])]
    public function rejeterFichier(
        PieceJointe $pieceJointe,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): RedirectResponse {
        if ($pieceJointe->getStatut() === 'Rejetée') {
            $this->addFlash('warning', 'Cette pièce a été rejetée. Veuillez la mettre à jour avant de la rejeter à nouveau.');
            return $this->redirectToRoute('app_demande_traiter', ['id' => $pieceJointe->getDemande()->getId()]);
        }

        $user = $this->getUser();
        $justification = $request->request->get('observation');

        $avisExistant = $entityManager->getRepository(PieceJointeAvisMembre::class)
            ->findOneBy(['piece' => $pieceJointe, 'membre' => $user]);

        if ($avisExistant) {
            $this->addFlash('warning', 'Vous avez déjà donné votre avis sur ce fichier.');
            return $this->redirectToRoute('app_demande_traiter', ['id' => $pieceJointe->getDemande()->getId()]);
        }

        $avis = new PieceJointeAvisMembre();
        $avis->setPiece($pieceJointe);
        $avis->setMembre($user);
        $avis->setFavorable(false);
        $avis->setJustification($justification);
        $avis->setActif(1);
        $avis->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($avis);

        $statut = $entityManager->getRepository(PieceJointeValidationStatut::class)
            ->findOneBy(['piece' => $pieceJointe]);

        if (!$statut) {
            $statut = new PieceJointeValidationStatut();
            $statut->setPiece($pieceJointe);
            $statut->setNbAvisTotal(0);
            $statut->setNbFavorable(0);
            $statut->setNbDefavorable(0); // Ajout du compteur d'avis défavorables
        }

        $statut->setNbAvisTotal($statut->getNbAvisTotal() + 1);
        $statut->setNbDefavorable($statut->getNbDefavorable() + 1); // Incrémentation du compteur d'avis défavorables

        $totalMembres = count($userRepository->findByRole('ROLE_COMITE_MEMBRE'));
        $demande = $pieceJointe->getDemande();

        if ($totalMembres > 0) {
            $ratioDefavorable = $statut->getNbDefavorable() / $totalMembres;

            if ($ratioDefavorable >= (2 / 3)) {
                $pieceJointe->setStatut('Rejetée');
                $statut->setStatutValidation('Rejetée');


                $emailProfessionnel = $demande->getProfessionnel()->getEmail();
                $professionnel = $demande->getProfessionnel()->getNom() . " " . $demande->getProfessionnel()->getPrenoms();
                $piece = $pieceJointe->getTypePiece()->getLibelle();

                $this->emailNotificationService->sendRejectionPieceNotification($emailProfessionnel, $justification, $professionnel, $piece);
            } else {
                $statut->setStatutValidation('En attente');
            }
        } else {
            $statut->setStatutValidation('En attente');
        }

        $entityManager->persist($statut);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis défavorable a été enregistré.');
        return $this->redirectToRoute('app_demande_traiter', ['id' => $demande->getId()]);
    }


    /*
     *     #[Route('/{id}/demande-valider', name: 'app_demande_valider', methods: ['POST'])]
    public function validerDemande(Request $request,Demande $demande, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        foreach ($demande->getPieceJointes() as $pieceJointe) {
            $pieceJointe->setStatut('Validée');
        }
        $demande->setStatut('Validée');
        $observation = $request->request->get('observation');
        // agent traitant
        $user = $this->getUser();
        $demande->setAgentTraitant($user);

        // date de validation
        $demande->setDateTraitement(new \DateTime());
        $demande->setObservation($observation);
        $entityManager->flush();

        $this->addFlash('success', 'La demande a été validée avec succès.');
        return $this->redirectToRoute('app_demande_en_attente');
    }

    #[Route('/{id}/demande-rejeter', name: 'app_demande_rejeter', methods: ['POST'])]
    public function rejeterDemande(Request $request, Demande $demande, EntityManagerInterface $entityManager, SessionInterface $session): RedirectResponse
    {
        foreach ($demande->getPieceJointes() as $pieceJointe) {
            $pieceJointe->setStatut('Rejetée');
        }
        $demande->setStatut('Rejetée');
        $observation = $request->request->get('observation');

        // agent traitant
        $user = $this->getUser();
        $demande->setAgentTraitant($user);
        $demande->setObservation($observation);
        $entityManager->flush();


        $emailProfessionnel = $demande->getProfessionnel()->getEmail();
        $professionnel= $demande->getProfessionnel()->getNom()." ".$demande->getProfessionnel()->getPrenoms();
        $observation = $demande->getObservation();
        // Envoyer l'email de notification de rejet
        $this->emailNotificationService->sendRejectionDemandeNotification($emailProfessionnel, $observation,$professionnel);

        $this->addFlash('success', 'La demande a été rejetée avec succès.');
        return $this->redirectToRoute('app_demande_index');
    }

     * */

    #[Route('/{id}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
    }

}
