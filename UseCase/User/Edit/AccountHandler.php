<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

declare(strict_types=1);

namespace BaksDev\Auth\Email\UseCase\User\Edit;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Messenger\AccountMessage;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Users\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccountHandler
{
    private EntityManagerInterface $entityManager;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    private MessageDispatchInterface $messageDispatch;

    private UserPasswordHasherInterface $userPasswordHasher;

    private ExistAccountByEmailInterface $existAccountByEmail;

    public function __construct(
        UserPasswordHasherInterface $userPasswordHasher,
        ExistAccountByEmailInterface $existAccountByEmail,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        MessageDispatchInterface $messageDispatch,
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->messageDispatch = $messageDispatch;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->existAccountByEmail = $existAccountByEmail;
    }

    /** @see Account */
    public function handle(
        AccountDTO $command,
        //?UploadedFile $cover = null
    ): string|Account
    {
        /**
         *  Валидация AccountDTO.
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);
            return $uniqid;
        }

        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(AccountEvent::class)->find(
                $command->getEvent()
            );

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    AccountEvent::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new AccountEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }

//        $this->entityManager->clear();
//        $this->entityManager->persist($Event);



        /**
         * Проверяем, имеется ли другой пользователь c таким Email.
         */
        $existAccount = $this->existAccountByEmail->isExistsEmail($command->getEmail(), $Event->getAccount());

        if($existAccount)
        {
            $uniqid = uniqid('', false);
            $this->logger->error(
                $uniqid.': '.sprintf(
                    'Пользователь с email %s уже имеется',
                    $command->getEmail()
                )
            );

            return $uniqid;
        }

        /*
         * Если Email был изменен - присваиваем статус NEW для подтверждения
         */
        if(!$command->getEmail()->isEqual($Event->getEmail()))
        {
            $Status = $command->getStatus();
            $Status->setStatus(new EmailStatus(EmailStatusNew::class));
        }

        /**
         * Если было изменение пароля
         */
        if(!empty($command->getPasswordPlain()))
        {
            $passwordNash = $this->userPasswordHasher->hashPassword(
                $Event,
                $command->getPasswordPlain()
            );

            /* Присваиваем новый пароль */
            $command->setPasswordHash($passwordNash);
        }

        /* @var Account $Account */
        if($Event->getAccount())
        {
            $Account = $this->entityManager
                ->getRepository(Account::class)
                ->findOneBy(['event' => $command->getEvent()]);

            if(empty($Account))
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by event: %s',
                    Account::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }
        }
        else
        {
            $usr = new User();
            $this->entityManager->persist($usr);

            $Account = new Account($usr);
            $this->entityManager->persist($Account);

            $Event->setMain($Account);
        }

        /* присваиваем событие корню */
        $Account->setEvent($Event);


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        /**
         * Валидация Account.
         */

        $errors = $this->validator->validate($Account);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        //$this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AccountMessage($Account->getId(), $Account->getEvent(), $command->getEvent()),
            transport: 'auth-email'
        );

        return $Account;
    }
}
