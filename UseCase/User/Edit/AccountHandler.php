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

declare(strict_types=1);

namespace BaksDev\Auth\Email\UseCase\User\Edit;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Messenger\AccountMessage;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusNew;
use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AccountHandler extends AbstractHandler
{
    private ExistAccountByEmailInterface $existAccountByEmail;
    private UserPasswordHasherInterface $userPasswordHasher;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
        ExistAccountByEmailInterface $existAccountByEmail,
        UserPasswordHasherInterface $userPasswordHasher,
        LoggerInterface $authEmailLogger
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);

        $this->existAccountByEmail = $existAccountByEmail;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->logger = $authEmailLogger;
    }


    /** @see Account */
    public function handle(
        AccountDTO $command
    ): string|Account
    {

        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new Account($command->getUsr());
        $this->event = new AccountEvent();


        /**
         * Если было изменение пароля
         */
        if(!empty($command->getPasswordPlain()))
        {
            $passwordNash = $this->userPasswordHasher->hashPassword(
                $this->event,
                $command->getPasswordPlain()
            );

            /* Присваиваем новый пароль */
            $command->setPasswordHash($passwordNash);
        }


        try
        {
            $command->getEvent() ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }


        /**
         * Проверяем, имеется ли другой пользователь c таким Email.
         */
        $existAccount = $this->existAccountByEmail->isExistsEmail($command->getEmail(), $this->event->getAccount());

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

        /**
         * Если Email был изменен - присваиваем статус NEW для подтверждения
         */
        if(!$command->getEmail()->isEqual($this->event->getEmail()))
        {
            $Status = $command->getStatus();
            $Status->setStatus(new EmailStatus(EmailStatusNew::class));
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
            transport: 'account'
        );

        return $this->main;

    }

}
