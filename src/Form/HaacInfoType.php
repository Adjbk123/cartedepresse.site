<?php

namespace App\Form;

use App\Entity\HaacInfo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HaacInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { $builder
        ->add('logoPath', FileType::class, [
            'label' => 'Logo de la HAAC',
            'mapped' => false,
            'required' => false,
        ])
        ->add('logoBeninPath', FileType::class, [
            'label' => 'Couleurs nationales du Bénin',
            'mapped' => false,
            'required' => false,
        ])
        ->add('amoiriePath', FileType::class, [
            'label' => "Armoiries du Bénin",
            'mapped' => false,
            'required' => false,
        ])
        ->add('cachetPath', FileType::class, [
            'label' => 'Cachet du Président de la HAAC',
            'mapped' => false,
            'required' => false,
        ])
        ->add('textIntro', TextareaType::class, [
            'label' => "Texte d'introduction (verso)",
            'required' => false,
        ])
        ->add('mentionFinale', TextareaType::class, [
            'label' => 'Mention finale',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HaacInfo::class,
        ]);
    }
}
