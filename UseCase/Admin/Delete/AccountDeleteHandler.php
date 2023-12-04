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

namespace BaksDev\Auth\Email\UseCase\Admin\Delete;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Messenger\AccountMessage;
use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccountDeleteHandler extends AbstractHandler
{
//    private EntityManagerInterface $entityManager;
//
//    private ValidatorInterface $validator;
//
//    private LoggerInterface $logger;
//
//    private MessageDispatchInterface $messageDispatch;
//
//    public function __construct(
//        EntityManagerInterface   $entityManager,
//        ValidatorInterface       $validator,
//        LoggerInterface          $logger,
//        MessageDispatchInterface $messageDispatch
//    )
//    {
//        $this->entityManager = $entityManager;
//        $this->validator = $validator;
//        $this->logger = $logger;
//        $this->messageDispatch = $messageDispatch;
//    }

    public function handle(AccountDeleteDTO $command): string|Account
    {
        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new Account();
        $this->event = new AccountEvent();

        try
        {
            $this->preRemove($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }


        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AccountMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'auth-email'
        );

        return $this->main;
    }



    /** @see Account */
    public function OLDhandle(
        AccountDeleteDTO $command,
    ): string|Account
    {
        /**
         *  Валидация AccountDTO.
         */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0) {
            $uniqid = uniqid('', false);
            $errorsString = (string)$errors;
            $this->logger->error($uniqid . ': ' . $errorsString);
            return $uniqid;
        }

        $EventRepo = $this->entityManager->getRepository(AccountEvent::class)->find(
            $command->getEvent()
        );

        if ($EventRepo === null) {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by id: %s',
                AccountEvent::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid . ': ' . $errorsString);

            return $uniqid;
        }

        $EventRepo->setEntity($command);
        $Event = $EventRepo->cloneEntity();
//        $this->entityManager->clear();
//        $this->entityManager->persist($Event);


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


        /* @var Account $Main */

        $Main = $this->entityManager->getRepository(Account::class)->findOneBy(
            ['event' => $command->getEvent()]
        );

        if (empty($Main)) {
            $uniqid = uniqid('', false);
            $errorsString = sprintf(
                'Not found %s by event: %s',
                Account::class,
                $command->getEvent()
            );
            $this->logger->error($uniqid . ': ' . $errorsString);

            return $uniqid;
        }


        /** Удаляем корень и сохраняем событие */
        $this->entityManager->remove($Main);
        $this->entityManager->flush();

        return $Main;
    }
}
