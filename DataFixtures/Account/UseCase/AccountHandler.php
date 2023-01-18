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

namespace BaksDev\Auth\Email\DataFixtures\Account\UseCase;

//use BaksDev\Users\AuthEmail\Account\Entity\Event\EventInterface;
//use BaksDev\Users\AuthEmail\Account\Type\Email\AccountEmail;
//use BaksDev\Users\AuthEmail\Account\Type\Event\AccountEvent;

use BaksDev\Auth\Email\DataFixtures\Account\UseCase\Status\StatusDTO;
use BaksDev\Auth\Email\Entity as EntityAccount;
use BaksDev\Users\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AccountHandler
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    private ValidatorInterface $validator;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface          $validator,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function handle(
        EntityAccount\Event\AccountEventInterface $command,
    ): string|EntityAccount\Account
    {
        /* Валидация */
        $errors = $this->validator->validate($command);

        if (count($errors) > 0) {
            $uniqid = uniqid('', false);
            $errorsString = (string) $errors;
            $this->logger->error($uniqid.': '.$errorsString);
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

        

        /* User */
        $User = new User();
        /* Account */
        $Account = new EntityAccount\Account($User);
        
		
        /* Присвиваем зависимости */
        $Event->setAccount($Account);
        $Account->setEvent($Event);
	
		/* AccountEvent */
		$Event->setEntity($command);
		
		$this->entityManager->persist($User);
		$this->entityManager->persist($Account);
		$this->entityManager->persist($Event);

        $this->entityManager->flush();
		
        return $Account;
    }

}

