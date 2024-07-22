<?php

namespace App\Form;

use App\Entity\President;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class PresidentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, )
            ->add('prenoms')
            ->add('datePriseFonction',)
            ->add('adresse')
            ->add('signature', DropzoneType::class,[
                'label'=>'Signature du président',
                'mapped' => false,
                'data_class' => null,
                'required'=>false

            ])
            ->add('cachet', DropzoneType::class,[
                'label'=> 'Cachet du président',
                'mapped' => false,
                'data_class' => null,
                'required'=>false

            ] )

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => President::class,
        ]);
    }
}
