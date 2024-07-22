<?php

namespace App\Form;

use App\Entity\Demande;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\UX\Dropzone\Form\DropzoneType;
use function Sodium\add;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateSoumission', null, [
                'widget' => 'single_text',
            ])
            ->add('statut')
            ->add('professionnel', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('casierJudiciaire', DropzoneType::class, [
                'label' => 'Téléverser ici vous casier judiciaire',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,
                'mapped'=>false,
            ])
            ->add('ficheDemande', DropzoneType::class, [
                'label' => 'Téléverser ici votre fiche de demande',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,

                'mapped'=>false,
            ])
            ->add('diplome', DropzoneType::class, [
                'label' => 'Téléverser ici votre diplôme',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,
                'mapped'=>false,
            ])
            ->add('quittance', DropzoneType::class, [
                'label' => 'Téléverser ici votre quittance de paiement',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,
                'mapped'=>false,
            ])
            ->add('attestationTravail', DropzoneType::class, [
                'label' => 'Téléverser ici votre attestation de travail',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,
                'mapped'=>false,
            ])
            ->add('cip', DropzoneType::class, [
                'label' => 'Téléverser ici votre certificat d\'identification personnel ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'data_class'=> null,
                'mapped'=>false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
