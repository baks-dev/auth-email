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

namespace BaksDev\Auth\Email\UseCase\Admin\NewEdit\Status;


use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class StatusForm extends AbstractType
{
    //    private $locale;
    //
    //    public function __construct(TranslatorInterface $translator)
    //    {
    //        $this->locale = $translator->getLocale();
    //    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'choices' => EmailStatus::cases(),
            'choice_value' => function(EmailStatus $status) {
                return $status->getEmailStatusValue();
            },
            'choice_label' => function(EmailStatus $status) {
                return $status->getEmailStatusValue();
            },
            'label' => false,
            'translation_domain' => 'account.status'
        ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults
        (
            [
                'data_class' => StatusDTO::class,
            ]);
    }

}
