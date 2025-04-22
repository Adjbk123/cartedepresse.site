<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\Dropzone\Form\DropzoneType;

class PersonnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'attr'=>[
                    'placeholder' => 'Nom',

                ]
            ] )
            ->add('prenoms', TextType::class, [
                'attr' => [
                    'placeholder' => 'Prénoms',

                ]
            ])
            ->add('typeCompte', ChoiceType::class,[
                'attr'=>[
                    'placeholder' => 'Type compte',
                    'class' => "select2"

                ],
                'choices'=>[
                    'Administrateur' => 'ROLE_ADMIN',
                    'Imprimeur' => 'ROLE_IMPRIMEUR',
                    'Agent de traitement de dossier' => 'ROLE_AGENT_TRAITEMENT',
                    'Chef service' => 'ROLE_CHEF_SERVICE',
                    'Autorité'=> 'ROLE_AUTORITE',
                    'Membre comité'=>"ROLE_COMITE_MEMBRE"

                ],

                'label' => 'Type de compte',
                'placeholder' => 'Choisir un type de compte',
                'mapped'=>false
            ] )
            ->add('email', EmailType::class,[
                'attr' => [
                    'placeholder' => 'Adresse email',

                ],
                'label'=> 'Adresse email',
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type'=> PasswordType::class,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                'first_options' => [
                    'label' => 'Mot de passe',

                ],
                'second_options' => [
                    'label' => 'Confirmer le mot de passe',

                ],
            ])

            ->add('dateNaissance', null, [
                'widget' => 'single_text',

            ])
            ->add('lieuNaissance')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Masculin' => 'Masculin',
                    'Féminin' => 'Féminin',
                ],
                'attr'=>[
                    'class' => "select2"
                ]
            ])
            ->add('photo', DropzoneType::class, [
                'label' => 'Photo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Faites glisser et déposez un fichier ou cliquez pour parcourir',
                ],
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
