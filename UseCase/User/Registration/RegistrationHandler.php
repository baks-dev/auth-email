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

namespace BaksDev\Auth\Email\UseCase\User\Registration;

//use BaksDev\Users\AuthEmail\Account\Entity\Event\EventInterface;
//use BaksDev\Users\AuthEmail\Account\Type\Email\AccountEmail;
//use BaksDev\Users\AuthEmail\Account\Type\Event\AccountEvent;

use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Messenger\Confirmation\ConfirmationAccountMessage;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RegistrationHandler
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $userPasswordHasher;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    private ExistAccountByEmailInterface $existAccountByEmail;

    private TranslatorInterface $translator;

    private MessageDispatchInterface $messageDispatch;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        ExistAccountByEmailInterface $existAccountByEmail,
        TranslatorInterface $translator,
        MessageDispatchInterface $messageDispatch,
    ) {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->existAccountByEmail = $existAccountByEmail;

        $this->translator = $translator;
        $this->messageDispatch = $messageDispatch;
    }

    public function handle(
        AccountEventInterface $command,
    ): string|EntityAccount\Account
    {
        /* Валидация DTO */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        /* Проверяем, имеется ли пользователь c таким Email */
        $existAccount = $this->existAccountByEmail->isExistsEmail($command->getEmail());

        if ($existAccount)
        {
            $uniqid = uniqid('', false);
            $this->logger->error(
                $uniqid.': '.sprintf(
                    'Пользователь с email %s уже зарегистрирован',
                    $command->getEmail()
                )
            );

            return $uniqid;
        }

        $this->entityManager->clear();

        $Event = new EntityAccount\Event\AccountEvent();

        /* Хешируем и присваиваем пароль */
        $passwordNash = $this->userPasswordHasher->hashPassword(
            $Event,
            $command->getPasswordPlain()
        );

        $command->setPasswordHash($passwordNash);

        /* AccountEvent */
        $Event->setEntity($command);
        $this->entityManager->persist($Event);

        /* User */
        $usr = new User();
        $this->entityManager->persist($usr);

        /* Account */
        $Account = new EntityAccount\Account($usr);
        $this->entityManager->persist($Account);

        /* При свиваем зависимости */
        $Event->setMain($Account);
        $Account->setEvent($Event);

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new ConfirmationAccountMessage($Account->getId(), $Account->getEvent(), new Locale($this->translator->getLocale())),
            transport: 'auth-email'
        );

        return $Account;
    }
}
