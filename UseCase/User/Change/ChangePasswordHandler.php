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

namespace BaksDev\Auth\Email\UseCase\User\Change;

use BaksDev\Auth\Email\Entity as EntityAccount;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ChangePasswordHandler
{
    public function __construct(
        #[Target('authEmailLogger')] private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher,
        private ValidatorInterface $validator
    ) {}


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
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [self::class.':'.__LINE__]);

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
