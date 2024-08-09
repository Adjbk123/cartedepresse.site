<?php

namespace App\Form;

use App\Entity\Organe;
use App\Entity\Profession;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\UX\Dropzone\Form\DropzoneType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Liste complète des nationalités en français
        $nationalities = [
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

        $builder
            ->add('nom')
            ->add('prenoms', TextType::class,[
                'label'=> 'Prénoms',
            ])
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
            ])
            ->add('lieuNaissance')
            ->add('npi', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un numéro NPI.',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Le numéro NPI doit comporter au moins {{ limit }} chiffres.',

                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/', // Regex pour accepter uniquement des chiffres
                        'message' => 'Le numéro NPI ne doit contenir que des chiffres positifs.',
                    ]),
                ],
                'label' => 'Numéro NPI', // Optionnel : ajouter un libellé
            ])
            ->add('sexe', ChoiceType::class, [
                'choices'=>[
                    'Masculin' => 'Masculin',
                    'Féminin' => 'Féminin',
                ],
                'placeholder'=>"Sélectionner le sexe",
                'attr'=> [
                    'class' => 'select2',
                ],

            ])
            ->add('nationalite', ChoiceType::class, [
                'choices' => $nationalities,
                'attr'=>[
                    'class' => 'select2',
                ],
                'data'=>"Béninois(e)"
            ])
            ->add('photo', DropzoneType::class, [
                'label' => 'Votre photo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'maxHeight'=> 900,
                        'maxWidth' => 1200,
                    ])
                ],
                'data_class'=> null,
                'help'=>"La photo doit mésurer 600x600 pixels et ne doit pas dépasser 5 Mo",
            ])
            ->add('organe',EntityType::class,[
                'choice_label'=>'designation',
                'class'=> Organe::class,
                'mapped'=>false,
                "attr"=>[
                    'class'=> 'select2',
                ],
                "required"=> true,
                "placeholder"=> "Choisir l'organe"
            ])
            ->add('profession',EntityType::class,[
                'choice_label'=>'libelle',
                'class'=> Profession::class,
                'mapped'=>false,
                "attr"=>[
                    'class'=> 'select2',
                ],
                "required"=> true,
                "placeholder"=> "Choisir la profession"
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
