<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder

            ->add(
                'title',
                TextType::class,
                [
                    'label' => 'Titre',
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )

            ->add(
                'description',
                TextareaType::class,
                [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => 8,
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )

            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Adresse e-mail',
                    'constraints' => [
                        new NotBlank(),
                        new Email(),
                    ],
                ]
            )

            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Envoyer',
                ]
            );
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {

        $resolver->setDefaults([]);
    }
}