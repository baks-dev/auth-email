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

use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Component\Validator\Constraints as Assert;

final class RegistrationDTO implements AccountEventInterface
{
    /** UserEvent ID */
    #[Assert\IsNull]
    private readonly ?AccountEventUid $id;

    /** Email */
    #[Assert\NotBlank]
    #[Assert\Email]
    private readonly AccountEmail $email;

    /** Дайджест Пароля */
    private readonly string $password;

    /** Пароль */
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 8,
        max: 4096
    )]
    private readonly string $passwordPlain;

    #[Assert\Valid]
    private Status\StatusDTO $status;

    /** Пользовательское соглашение */
    #[Assert\IsTrue()]
    public ?bool $agreeTerms = true;


    public function __construct()
    {
        $this->id = null;
        $this->status = new Status\StatusDTO();

    }


    public function setId(AccountEventUid $id): void {}


    public function getEvent(): ?AccountEventUid
    {
        return $this->id;
    }


    /** Email */

    public function getEmail(): AccountEmail
    {
        return $this->email;
    }


    public function setEmail(AccountEmail $email): void
    {
        $this->email = $email;
    }

    /* Статус */

    /**
     * @return Status\StatusDTO
     */
    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }

    //    /**
    //     * @param Status\StatusDTO $status
    //     */
    //    public function setStatus(Status\StatusDTO $status) : void
    //    {
    //        $this->status = $status;
    //    }

    /** Текстовый пароль */

    public function getPasswordPlain(): ?string
    {
        return $this->passwordPlain;
    }


    public function setPasswordPlain(?string $passwordPlain): void
    {
        $this->passwordPlain = $passwordPlain;
    }


    /** Хешированный пароль */

    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function setPasswordHash(string $password): void
    {
        $this->password = $password;
    }

}
