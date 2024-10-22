<?php

namespace App\Service;

use App\Entity\Lot;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailNotificationService
{
    private $mailer;
    private $twig;
    private $userRepository;

    public function __construct(MailerInterface $mailer, Environment $twig,UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->userRepository = $userRepository;
    }

    public function sendDemandSubmissionEmail(string $toEmail, array $demandeData): void
    {
        $subject = 'Bravo ! Votre demande a été envoyée avec succès';
        $htmlTemplate = 'emails/demande_soumission.html.twig';
        $context = [
            'nom' => $demandeData['nom'],
            'demande' => $demandeData,
        ];

        $htmlContent = $this->twig->render($htmlTemplate, $context);

        $email = (new Email())
            ->from('Support Carte de Presse <support@cartedepresse.site>')
            ->to($toEmail)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }


    public function sendRejectionDemandeNotification(string $recipientEmail, string $observation, string $professionnel): void
    {
        $subject = 'Notification de rejet de votre demande';
        $htmlTemplate = 'emails/demande_rejet.html.twig';
        $context = [
            'observation' => $observation,
            'professionnel' => $professionnel
        ];
        $htmlContent = $this->twig->render($htmlTemplate, $context);



        $email = (new Email())
            ->from('Support Carte de Presse <support@cartedepresse.site>')
            ->to($recipientEmail) // Adresse email du destinataire
            ->subject($subject) // Objet de l'email
            ->html($htmlContent); // Corps de l'email au format HTML

        $this->mailer->send($email); // Envoi de l'email
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendMessageCompletionProfil(string $recipientEmail, array $professionnel): void
    {
        // Sujet de l'email
        $subject = 'Invitation à compléter votre profil';

        // Template HTML pour l'email
        $htmlTemplate = 'emails/profilCompletion.html.twig';

        // Contexte à passer au template (nom et prénoms du professionnel)
        $context = [
            'professionnel' => $professionnel // Cela doit être un tableau avec les clés 'nom' et 'prenoms'
        ];

        // Génération du contenu HTML de l'email avec Twig
        $htmlContent = $this->twig->render($htmlTemplate, $context);

        // Création de l'email
        $email = (new Email())
            ->from('Support Carte de Presse <support@cartedepresse.site>') // Adresse expéditeur
            ->to($recipientEmail) // Adresse email du destinataire
            ->subject($subject) // Objet de l'email
            ->html($htmlContent); // Corps de l'email au format HTML

        // Envoi de l'email
        $this->mailer->send($email);
    }
    public function sendAccountCreationNotification(
        string $nom,
        string $prenoms,
        string $recipientEmail,
        string $username,
        string $password,
        string $numeroDemande
    ): void {
        $subject = 'Votre dossier de demande de carte de presse a été enregistré';
        $htmlTemplate = 'emails/compte_cree.html.twig';

        // Le contexte avec les variables dynamiques à passer au template
        $context = [
            'nom'=>$nom,
            "prenoms"=>$prenoms,
            'email' => $recipientEmail,
            'username' => $username,
            'password' => $password,
            'numeroDemande' => $numeroDemande,
            'lienConnexion' => 'https://cartedepresse.site/login',
            'lienSuiviDemande' => 'https://cartedepresse.site/demande/suivie'
        ];

        // Rendu du contenu de l'email à partir du template Twig
        $htmlContent = $this->twig->render($htmlTemplate, $context);

        // Création de l'email avec le contenu HTML et l'objet
        $email = (new Email())
            ->from('Support Carte de Presse <support@cartedepresse.site>') // Adresse expéditeur
            ->to($recipientEmail) // Adresse du destinataire
            ->subject($subject) // Objet de l'email
            ->html($htmlContent); // Contenu de l'email au format HTML

        // Envoi de l'email
        $this->mailer->send($email);
    }


    public function sendRejectionPieceNotification(string $recipientEmail, string $observation, string $professionnel, string $piece): void
    {
        $subject = 'Notification de rejet de votre pièce jointe';
        $htmlTemplate = 'emails/fichier_demande_rejet.html.twig';
        $context = [
            'observation' => $observation,
            'piece'=> $piece,
            "professionnel"=>$professionnel
        ];
        $htmlContent = $this->twig->render($htmlTemplate, $context);



        $email = (new Email())
            ->from('Support Carte de Presse <support@cartedepresse.site>')
            ->to($recipientEmail) // Adresse email du destinataire
            ->subject($subject) // Objet de l'email
            ->html($htmlContent); // Corps de l'email au format HTML

        $this->mailer->send($email); // Envoi de l'email
    }


    public function sendLotCreatedEmail(Lot $lot)
    {
        // Récupérer tous les utilisateurs avec le rôle ROLE_AUTORITE
        $users = $this->userRepository->findByRole('ROLE_AUTORITE');

        foreach ($users as $user) {
            $toEmail = $user->getEmail();
            $subject = 'Nouveau Lot de Demandes Créé';
            $htmlTemplate = 'emails/lot_created.html.twig';

            $htmlContent = $this->twig->render($htmlTemplate, [
                'lot' => $lot,
                'user' => $user,
            ]);

            $email = (new Email())
                ->from('Support Carte de Presse <support@cartedepresse.site>')
                ->to($toEmail)
                ->subject($subject)
                ->html($htmlContent);

            $this->mailer->send($email);
        }
    }
}
