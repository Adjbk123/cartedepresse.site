<?php

namespace App\Form;

use App\Entity\Demande;
use App\Entity\DuplicataDemande;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class DuplicataDemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('declarationPerte', DropzoneType::class, [
                'label' => 'Déclaration de perte',
                'help' => 'Copie intégrale de la déclaration de perte',
            ])
            ->add('cip', DropzoneType::class, [
                'label' => 'CIP',
                'help' => 'Copie intégrale de la pièce justificative',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DuplicataDemande::class,
        ]);
    }
}
