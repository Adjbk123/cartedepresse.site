<?php

namespace App\Form;

use App\Entity\Promoteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromoteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenoms', TextType::class,[
                'label'=> 'Prénoms',
            ])
            ->add('contact')
            ->add('adresse')
            ->add('photo', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'data_class' => null,
                'required'=>false
            ])

            ->add('dateNaissance', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('lieuNaissance')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Promoteur::class,
        ]);
    }
}
