<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control h-48px w-full bg-white dark:border-white dark:bg-opacity-10 dark:border-opacity-0 dark:text-white",
                    'placeholder' => 'Nom Complet',
                    'required' => true,
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control h-48px w-full bg-white dark:border-white dark:bg-opacity-10 dark:border-opacity-0 dark:text-white",
                    'placeholder' => 'Votre Email',
                    'required' => true,
                ],
            ])
            ->add('subject',TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control h-48px w-full bg-white dark:border-white dark:bg-opacity-10 dark:border-opacity-0 dark:text-white w-100 my-5",
                    'placeholder' => 'Sujet',
                    'required' => true,
                ],
                ])
            ->add('message',TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => "form-control min-h-150px w-full bg-white dark:border-white dark:bg-opacity-10 dark:border-opacity-0 dark:text-white",
                    'placeholder' => 'Message',
                    'required' => true,
                ],
                ])
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Laisser un message',
            //     'attr' => [
            //         'class' => "btn btn-primary btn-md text-white mt-2 w-100 mb-2"
            //     ],
            //     ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
