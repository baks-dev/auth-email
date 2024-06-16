<?php

namespace BaksDev\Auth\Email\UseCase\User\Change;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('passwordPlain', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],

                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'user.danger.match',

            ]);

        /** Регистрация */
        $builder->add(
            'change',
            SubmitType::class,
            ['label' => 'Change', 'label_html' => true]
        );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ChangePasswordDTO::class,
                'translation_domain' => 'user.reset',
            ]
        );
    }

}
