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

namespace BaksDev\Auth\Email\UseCase\User\Edit;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** @see AccountEvent */
final class AccountDTO implements AccountEventInterface
{
    #[Assert\Uuid]
    private ?AccountEventUid $id = null;

    /** Email */
    #[Assert\NotBlank]
    #[Assert\Email]
    private AccountEmail $email;

    /** Пароль */
    #[Assert\Length(
        min: 8,
        max: 4096
    )]
    private ?string $passwordPlain = null;

    /* Если пользователь не меняет пароль - сохраняем предыдущий */

    private ?string $password = null;


    private ?UserUid $usr = null;


    #[Assert\Valid]
    private Status\StatusDTO $status;


    public function __construct()
    {
        $this->status = new Status\StatusDTO();
    }


    public function setId(?AccountEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?AccountEventUid
    {
        return $this->id;
    }

    /* Email */


    public function getEmail(): AccountEmail
    {
        return $this->email;
    }


    public function setEmail(AccountEmail $email): void
    {
        $this->email = $email;
    }

    /* Статус */

    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }

    public function setStatus(Status\StatusDTO $status): void
    {
        $this->status = $status;
    }

    /* Пароль */

    public function getPasswordPlain(): ?string
    {
        return $this->passwordPlain;
    }

    public function setPasswordPlain(?string $passwordPlain): void
    {
        $this->passwordPlain = $passwordPlain;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function setPasswordHash(string $password): void
    {
        $this->password = $password;
    }

    #[Assert\Callback]
    public function validatePassword(ExecutionContextInterface $context, $payload): void
    {
        if(empty($this->passwordPlain) && empty($this->password))
        {
            $context->buildViolation('assert.password.empty')
                ->atPath('passwordPlain')
                ->addViolation();
        }
    }

    /**
     * Usr
     */
    public function getUsr(): ?UserUid
    {
        return $this->usr;
    }

    public function setUsr(UserUid $usr): self
    {
        $this->usr = $usr;
        return $this;
    }

}
