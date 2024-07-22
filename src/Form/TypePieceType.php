<?php

namespace App\Form;

use App\Entity\TypePiece;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypePieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('typeDemande', ChoiceType::class,[
                'choices'=>[
                    'Nouvelle Demande de carte' => 'Nouveau',
                    'Renouvellement de carte' => 'Renouvellement ',
                ],
                'attr'=>[
                    'class'=>'select2'
                ]
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypePiece::class,
        ]);
    }
}
