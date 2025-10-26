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


    public function setEmail(AccountEmail $email): self
    {
        $this->email = $email;
        return $this;
    }

    /* Статус */

    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }

    public function setStatus(Status\StatusDTO $status): self
    {
        $this->status = $status;
        return $this;
    }

    /* Пароль */

    public function getPasswordPlain(): ?string
    {
        return $this->passwordPlain;
    }

    public function setPasswordPlain(?string $passwordPlain): self
    {
        $this->passwordPlain = $passwordPlain;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setPasswordHash(string $password): self
    {
        $this->password = $password;
        return $this;
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
