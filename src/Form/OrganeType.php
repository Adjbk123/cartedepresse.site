<?php

namespace App\Form;

use App\Entity\Organe;
use App\Entity\Promoteur;
use App\Entity\TypeOrgane;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
            ])
            ->add('adresse', TextType::class, [
                'required'=> false,
            ])
            ->add('contact', TelType::class, [
                'label' => 'Contact',
                'required'=>false

            ])
            ->add('latitude', TextType::class, [
                'label'=>"Latitude",
                'required' => false
            ])
            ->add('longitude', TextType::class,[
                'label'=>"Longitude",
                'required' => false
            ])
            ->add('commune', ChoiceType::class, [
                'choices' => [
                    'Alibori' => [
                        'Banikoara' => 'Banikoara',
                        'Gogounou' => 'Gogounou',
                        'Kandi' => 'Kandi',
                        'Karimama' => 'Karimama',
                        'Malanville' => 'Malanville',
                        'Segbana' => 'Segbana',
                    ],
                    'Atacora' => [
                        'Boukoumbé' => 'Boukoumbé',
                        'Cobly' => 'Cobly',
                        'Kérou' => 'Kérou',
                        'Kouandé' => 'Kouandé',
                        'Matéri' => 'Matéri',
                        'Natitingou' => 'Natitingou',
                        'Péhunco' => 'Péhunco',
                        'Tanguiéta' => 'Tanguiéta',
                        'Toucountouna' => 'Toucountouna',
                    ],
                    'Atlantique' => [
                        'Abomey-Calavi' => 'Abomey-Calavi',
                        'Allada' => 'Allada',
                        'Kpomassè' => 'Kpomassè',
                        'Ouidah' => 'Ouidah',
                        'Sô-Ava' => 'Sô-Ava',
                        'Toffo' => 'Toffo',
                        'Tori-Bossito' => 'Tori-Bossito',
                        'Zè' => 'Zè',
                    ],
                    'Borgou' => [
                        'Bembèrèkè' => 'Bembèrèkè',
                        'Kalalé' => 'Kalalé',
                        'N\'Dali' => 'N\'Dali',
                        'Nikki' => 'Nikki',
                        'Parakou' => 'Parakou',
                        'Pèrèrè' => 'Pèrèrè',
                        'Sinendé' => 'Sinendé',
                        'Tchaourou' => 'Tchaourou',
                    ],
                    'Collines' => [
                        'Bantè' => 'Bantè',
                        'Dassa-Zoumè' => 'Dassa-Zoumè',
                        'Glazoué' => 'Glazoué',
                        'Ouèssè' => 'Ouèssè',
                        'Savalou' => 'Savalou',
                        'Savè' => 'Savè',
                    ],
                    'Donga' => [
                        'Bassila' => 'Bassila',
                        'Copargo' => 'Copargo',
                        'Djougou' => 'Djougou',
                        'Ouaké' => 'Ouaké',
                    ],
                    'Kouffo' => [
                        'Aplahoué' => 'Aplahoué',
                        'Djakotomey' => 'Djakotomey',
                        'Dogbo' => 'Dogbo',
                        'Klouékanmè' => 'Klouékanmè',
                        'Lalo' => 'Lalo',
                        'Toviklin' => 'Toviklin',
                    ],
                    'Littoral' => [
                        'Cotonou' => 'Cotonou',
                    ],
                    'Mono' => [
                        'Athiémé' => 'Athimé',
                        'Bopa' => 'Bopa',
                        'Comè' => 'Comè',
                        'Grand-Popo' => 'Grand-Popo',
                        'Houéyogbé' => 'Houéyogbé',
                        'Lokossa' => 'Lokossa',
                    ],
                    'Ouémé' => [
                        'Adjarra' => 'Adjarra',
                        'Adjohoun' => 'Adjohoun',
                        'Aguégués' => 'Aguégués',
                        'Akpro-Missérété' => 'Akpro-Missérété',
                        'Avrankou' => 'Avrankou',
                        'Bonou' => 'Bonou',
                        'Dangbo' => 'Dangbo',
                        'Porto-Novo' => 'Porto-Novo',
                        'Sèmè-Kpodji' => 'Sèmè-Kpodji',
                    ],
                    'Plateau' => [
                        'Ifangni' => 'Ifangni',
                        'Kétou' => 'Kétou',
                        'Pobè' => 'Pobè',
                        'Sakété' => 'Sakété',
                        'Adja-Ouèrè' => 'Adja-Ouèrè',
                    ],
                    'Zou' => [
                        'Abomey' => 'Abomey',
                        'Agbangnizoun' => 'Agbangnizoun',
                        'Bohicon' => 'Bohicon',
                        'Covè' => 'Covè',
                        'Djidja' => 'Djidja',
                        'Ouinhi' => 'Ouinhi',
                        'Zagnanado' => 'Zagnanado',
                        'Za-Kpota' => 'Za-Kpota',
                        'Zogbodomey' => 'Zogbodomey',
                    ],
                ],
                'attr'=>[
                    'class'=>"select2"
                ]
            ])
            ->add('promoteur', EntityType::class, [
                'class' => Promoteur::class,
                'attr'=>[
                    'class'=>"select2"
                ]
            ]) ->add('typeOrgane', EntityType::class, [
                'class' => TypeOrgane::class,
                'choice_label' => 'libelle',
                'attr'=>[
                    'class'=>"select2"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organe::class,
        ]);
    }
}
