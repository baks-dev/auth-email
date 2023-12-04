<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Auth\Email\UseCase\Admin\Delete;

use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use Symfony\Component\Validator\Constraints as Assert;
use BaksDev\Auth\Email\Entity\Event\AccountEventInterface;

/** @see AccountEvent */
final class AccountDeleteDTO implements AccountEventInterface
{
    /**
     * Идентификатор события.
     */
    #[Assert\Uuid]
    private readonly AccountEventUid $id;

    /**
     * Статус
     */
    #[Assert\Valid]
    private Status\StatusDTO $status;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    private Modify\ModifyDTO $modify;


    public function __construct()
    {
        $this->status = new Status\StatusDTO();
        $this->modify = new Modify\ModifyDTO();
    }

    /**
     * Идентификатор события.
     */
    public function getEvent(): ?AccountEventUid
    {
        return $this->id;
    }

    public function setId(AccountEventUid $id): void
    {
        $this->id = $id;
    }

    /**
     * Статус
     */
    public function getStatus(): Status\StatusDTO
    {
        return $this->status;
    }

    /**
     * Modify
     */
    public function getModify(): Modify\ModifyDTO
    {
        return $this->modify;
    }

}
