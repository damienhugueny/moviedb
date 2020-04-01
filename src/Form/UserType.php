<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', null, [
                'empty_data' => ''
            ])
            //->add('roles')
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                // => car fonction anonyme, on doit récupérer ces deux objets via l'event
                // L'event a connaissance de ces deux objets et on peut y accéder
                // On récupère l'entité contenue dans le Form
                $user = $event->getData();
                // On récupère le form
                $form = $event->getForm();

                // Le user est-il nouveau ?
                if ($user->getId() === null) {
                    $form->add('password', PasswordType::class, [
                        'empty_data' => '',
                    ]);
                } else {
                    // Champ non mappé
                    $form->add('password', PasswordType::class, [
                        'mapped' => false,
                    ]);
                }
            })

            // Pour rappel, si je souhaite gerer des listes a choix multiple ou non je dois utiliser ChoiceType et non check pour gerer ceci
            // note pour gerer plusieurs affichage je dois jouer avec les parametre multiple + expanded cf doc
            ->add('userRoles', EntityType::class, [
                'class' => Role::class,
                'choice_name' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ]
        ]);
    }
}
