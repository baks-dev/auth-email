<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Auth\Email\UseCase\User\Registration;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Captcha\Security\CaptchaVerify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RegistrationForm extends AbstractType
{
    public function __construct(private readonly CaptchaVerify $captchaVerify) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Email */
        $builder->add('email', EmailType::class);

        $builder->get('email')->addModelTransformer(
            new CallbackTransformer(
                function($email) {
                    return $email instanceof AccountEmail ? $email->getValue() : $email;
                },
                function($email) {
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
        $builder->add('agreeTerms', CheckboxType::class, ['required' => true]);


        $builder->add('code', TextType::class);

        $builder->get('code')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event): void {

                $code = $event->getForm()->getData();
                $verify = $this->captchaVerify->verify($code);

                /** @var RegistrationDTO $RegistrationDTO */
                $RegistrationDTO = $event->getForm()->getParent()?->getData();

                if($verify)
                {
                    $RegistrationDTO->captchaValid();
                }
            }
        );

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
