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

use BaksDev\Auth\Email\DataFixtures\Account\UseCase\Status\StatusDTO;
use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Users\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegistrationHandler
{
    private EntityManagerInterface $entityManager;
    //private ImageUploadInterface $imageUpload;
    private UserPasswordHasherInterface $userPasswordHasher;
    private ValidatorInterface $validator;

    //private CountUserAccountByEmailInterface $countUserAccountByEmail;
    private LoggerInterface $logger;
    private ExistAccountByEmailInterface $existAccountByEmail;

    public function __construct(
        EntityManagerInterface       $entityManager,
        //ImageUploadInterface $imageUpload,
        UserPasswordHasherInterface  $userPasswordHasher,
        ValidatorInterface           $validator,
        LoggerInterface              $logger,
        ExistAccountByEmailInterface $existAccountByEmail
        //CountUserAccountByEmailInterface $countUserAccountByEmail,
    )
    {
        $this->entityManager = $entityManager;
        //$this->imageUpload = $imageUpload;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validator = $validator;
        //$this->countUserAccountByEmail = $countUserAccountByEmail;
        $this->logger = $logger;
        $this->existAccountByEmail = $existAccountByEmail;
    }

    public function handle(
        RegistrationDTO $command,
        //?UploadedFile $cover = null
    ): string|EntityAccount\Account
    {
        /* Валидация */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0) {
            $uniqid = uniqid('', false);
            $errorsString = (string)$errors;
            $this->logger->error($uniqid . ': ' . $errorsString);
            return $uniqid;
        }

        /* Проверяем, имеется ли пользователь c таким Email */
        $existAccount = $this->existAccountByEmail->get($command->getEmail());

        if ($existAccount) {
            $uniqid = uniqid('', false);
            $this->logger->error($uniqid . ': ' . sprintf('Пользователь с email %s уже зарегистрирован', $command->getEmail()));
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
        $User = new User();
        $this->entityManager->persist($User);

        /* Account */
        $Account = new EntityAccount\Account($User);
        $this->entityManager->persist($Account);

        /* Присвиваем зависимости */
        $Event->setAccount($Account);
        $Account->setEvent($Event);

        $this->entityManager->flush();

        return $Account;
    }

}

