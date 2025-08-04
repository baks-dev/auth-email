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


    public function getEvent(): AccountEventUid
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