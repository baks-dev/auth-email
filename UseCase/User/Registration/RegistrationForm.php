<?php

namespace BaksDev\Auth\Email\UseCase\User\Registration;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Email */
        $builder->add('email', EmailType::class);

        $builder->get('email')->addModelTransformer(
            new CallbackTransformer(
                function ($email) {
                    return $email instanceof AccountEmail ? $email->getValue() : $email;
                },
                function ($email) {
                    return new AccountEmail($email);
                }
            )
        );

        /** Пароль */
        $builder->add('passwordPlain', PasswordType::class, [
            'required' => true,
            'attr' => ['autocomplete' => "new-password"],
        ]);

        /** Пользовательское соглашение */
        $builder
            ->add('agreeTerms', CheckboxType::class, ['required' => true])
        ;

        /** Регистрация */
        $builder->add(
            'registration',
            SubmitType::class,
            ['label' => 'Registration', 'label_html' => true]
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => RegistrationDTO::class,
                'attr' => ['autocomplete' => "off"],
            ]
        );
    }

}
