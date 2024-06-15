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

namespace BaksDev\Auth\Email\UseCase\User\Change;

use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Component\Validator\Constraints as Assert;

final class ChangePasswordDTO implements AccountEventInterface
{
    /** Идентификатор  */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly AccountEventUid $id;

    /** Дайджест Пароля */
    #[Assert\Length(max: 4096)]
    private readonly string $password;

    /** Сброс пароля */
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 8,
        max: 4096
    )]
    private readonly string $passwordPlain;

    #[Assert\Valid]
    private readonly Status\StatusDTO $status;


    public function __construct(AccountEventUid $id)
    {
        $this->status = new Status\StatusDTO();
        $this->id = $id;
    }


    /** Идентификатор  */
    public function setId(AccountEventUid $id): void
    {
        //$this->id = $id;
    }


    public function getEvent(): ?AccountEventUid
    {
        return $this->id;
    }


    /** Статус */
    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }


    /** Сброс пароля */
    public function getPasswordPlain(): string
    {
        return $this->passwordPlain;
    }


    /**
     * @param string $passwordPlain
     */
    public function setPasswordPlain(string $passwordPlain): void
    {
        $this->passwordPlain = $passwordPlain;
    }


    /** Дайджест Пароля */

    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function setPasswordHash(string $password): void
    {
        $this->password = $password;
    }

}
