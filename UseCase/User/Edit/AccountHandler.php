<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AccountHandler extends AbstractHandler
{
    public function __construct(
        #[Target('authEmailLogger')] private readonly LoggerInterface $logger,
        private readonly ExistAccountByEmailInterface $existAccountByEmailRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,

        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);
    }


    /** @see Account */
    public function handle(AccountDTO $command): string|Account
    {
        $this
            ->setCommand($command)
            ->preEventPersistOrUpdate(new Account($command->getUsr()), AccountEvent::class);

        /**
         * Проверяем, имеется ли другой пользователь c таким Email.
         */

        $existAccount = $this->existAccountByEmailRepository
            ->fromEmail($command->getEmail())
            ->fromUser($this->event?->getAccount())
            ->isExists();

        if($existAccount)
        {
            $uniqid = uniqid('', false);
            $this->logger->error(
                $uniqid.': '.sprintf(
                    'Пользователь с email %s уже зарегистрирован',
                    $command->getEmail(),
                ),
            );

            return $uniqid;
        }


        /**
         * Если было изменение пароля
         *
         * @var AccountEvent $this ->event
         */
        if(false === empty($command->getPasswordPlain()))
        {
            $passwordNash = $this->userPasswordHasher->hashPassword(
                $this->event,
                $command->getPasswordPlain(),
            );

            /* Присваиваем новый пароль */
            $command->setPasswordHash($passwordNash);
            $this->event->setEntity($command);
        }


        /**
         * Если Email был изменен - присваиваем статус NEW для подтверждения
         */
        if(false === $command->getEmail()->isEqual($this->event->getEmail()))
        {
            $Status = $command->getStatus();
            $Status->setStatus(new EmailStatus(EmailStatusNew::class));
            $this->event->setEntity($command);
        }


        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AccountMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'auth-email',
        );

        return $this->main;

    }

}
