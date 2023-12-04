<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Auth\Email\UseCase\User\Edit;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /*
         * Email
         */
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

        /*
         *  Пароль
         */
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            /** @var AccountDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            $form->add('passwordPlain', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => !$data->getEvent(),
            ]);
        });

        /*
         * Сохранить
         */
        $builder->add(
            'account',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AccountDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100', 'autocomplete' => "off"],
            ]
        );
    }
}
