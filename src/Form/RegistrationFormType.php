<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control h-48px w-full bg-white dark:bg-opacity-0 dark:text-white dark:border-gray-300 dark:border-opacity-30",
                    'placeholder' => 'Email'
                    ]
            ])
            ->add('pseudo', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control h-48px w-full bg-white dark:bg-opacity-0 dark:text-white dark:border-gray-300 dark:border-opacity-30",
                    'placeholder' => 'Pseudo'
                    ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => false,
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'class' => 'form-control h-48px w-full bg-white dark:bg-opacity-0 dark:text-white dark:border-gray-300 dark:border-opacity-30',
                    'autocomplete' => 'new-password',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisissez un mot de passe s\'il vous plaît',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-check-input rounded bg-white dark:bg-opacity-0 dark:text-white dark:border-gray-300 dark:border-opacity-30',
                ],  
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions d\'utilisation.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
