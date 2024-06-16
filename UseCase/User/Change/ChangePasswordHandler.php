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

namespace BaksDev\Auth\Email\UseCase\User\Change;

use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ChangePasswordHandler
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private ExistAccountByEmailInterface $existAccountByEmail;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        ExistAccountByEmailInterface $existAccountByEmail,
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->existAccountByEmail = $existAccountByEmail;
    }


    public function handle(
        ChangePasswordDTO $command,
        //?UploadedFile $cover = null
    ): string|EntityAccount\Account
    {
        /* Валидация DTO */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        $EventRepo = $this->entityManager->getRepository(EntityAccount\Event\AccountEvent::class)
            ->find($command->getEvent());

        if($EventRepo === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf('Ошибка при сбросе пароля сущности AccountEvent с id: %s', $command->getEvent());
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        $EventRepo->setEntity($command);
        $EventRepo->setEntityManager($this->entityManager);
        $Event = $EventRepo->cloneEntity();
        //        $this->entityManager->clear();
        //        $this->entityManager->persist($Event);

        /* Хешируем и присваиваем пароль */
        $passwordNash = $this->userPasswordHasher->hashPassword(
            $Event,
            $command->getPasswordPlain()
        );
        $command->setPasswordHash($passwordNash);


        /* Account */
        $Account = $this->entityManager->getRepository(EntityAccount\Account::class)->findOneBy(
            ['event' => $command->getEvent()]
        );

        if($Account === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Ошибка при сбросе пароля сущности Account с событием event: %s',
                $command->getEvent()
            );
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        /* Присвиваем зависимости */
        $Event->setMain($Account);
        $Account->setEvent($Event);

        $this->entityManager->flush();

        return $Account;
    }

}
