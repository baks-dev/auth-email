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
    #[Assert\NotBlank]
    public bool $agreeTerms = false;

    /** Пользовательское соглашение */
    #[Assert\NotBlank]
    private bool $captcha = false;

    /** Пользовательское соглашение */
    #[Assert\NotBlank]
    public ?string $code = null;

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

    public function captchaValid(): void
    {
        $this->captcha = true;
    }

    /**
     * Code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

}
