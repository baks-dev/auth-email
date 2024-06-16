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

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Messenger\Confirmation\ConfirmationAccountMessage;
use BaksDev\Auth\Email\Repository\ExistAccountByEmail\ExistAccountByEmailInterface;
use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Locale\Locales\Ru;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationHandler extends AbstractHandler
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private ExistAccountByEmailInterface $existAccountByEmail;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
        UserPasswordHasherInterface $userPasswordHasher,
        ExistAccountByEmailInterface $existAccountByEmail,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);

        $this->userPasswordHasher = $userPasswordHasher;
        $this->existAccountByEmail = $existAccountByEmail;
    }


    public function handle(RegistrationDTO $command): string|Account
    {
        /* Проверяем, имеется ли пользователь c таким Email */
        $existAccount = $this->existAccountByEmail->isExistsEmail($command->getEmail());

        if($existAccount)
        {
            $this->validatorCollection->error(sprintf('Пользователь с email %s уже зарегистрирован', $command->getEmail()));
            return $this->validatorCollection->getErrorUniqid();
        }

        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        /* User */
        $User = new User();
        $this->main = new Account($User);
        $this->event = new AccountEvent();

        /* Хешируем и присваиваем пароль */
        $passwordNash = $this->userPasswordHasher->hashPassword(
            $this->event,
            $command->getPasswordPlain()
        );

        $command->setPasswordHash($passwordNash);

        try
        {
            $this->prePersist($command);
            $this->entityManager->persist($User);
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
            message: new ConfirmationAccountMessage($this->main->getId(), $this->main->getEvent(), new Locale(Ru::class)),
            transport: 'account'
        );

        return $this->main;
    }
}
