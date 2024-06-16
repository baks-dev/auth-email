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

namespace BaksDev\Auth\Email\UseCase\User\Verify;

use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Auth\Email\Messenger\AccountMessage;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class VerifyHandler
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;
    private MessageDispatchInterface $messageDispatch;


    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        MessageDispatchInterface $messageDispatch
    )
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->messageDispatch = $messageDispatch;
    }


    public function handle(
        VerifyDTO $command,
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

        $EventRepo = $this->entityManager->getRepository(EntityAccount\Event\AccountEvent::class)->find(
            $command->getEvent()
        );

        if($EventRepo === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf('Ошибка при активации сущности AccountEvent с id: %s', $command->getEvent());
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }


        /* AccountEvent */
        $EventRepo->setEntity($command);
        $EventRepo->setEntityManager($this->entityManager);
        $Event = $EventRepo->cloneEntity();


        /* Account */
        $Account = $this->entityManager->getRepository(EntityAccount\Account::class)->findOneBy(
            ['event' => $command->getEvent()]
        );

        if($Account === null)
        {
            $uniqid = uniqid('', false);
            $errorsString = sprintf('Ошибка при активации сущности Account с событием event: %s', $command->getEvent());
            $this->logger->error($uniqid.': '.$errorsString);

            return $uniqid;
        }

        /* Присвиваем зависимости */
        $Event->setMain($Account);
        $Account->setEvent($Event);

        $this->entityManager->flush();


        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AccountMessage($Account->getId(), $Account->getEvent(), $command->getEvent()),
            transport: 'auth-email'
        );

        return $Account;
    }

}
