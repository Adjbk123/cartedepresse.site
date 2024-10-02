<?php

namespace App\Service;

use App\Entity\Lot;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

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

    public function sendDemandSubmissionEmail(string $toEmail, array $demandeData)
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


    public function sendRejectionDemandeNotification(string $recipientEmail, string $observation, string $professionnel)
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
    public function sendRejectionPieceNotification(string $recipientEmail, string $observation, string $professionnel, string $piece)
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
